<?php

namespace App\Service;

use App\Entity\ContratLigne;
use App\Entity\Imprimante;
use App\Repository\FacturationPeriodeRepository;
use App\Repository\ReleveCompteurRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour calculer les estimations de facturation basées sur la consommation moyenne
 */
class BillingEstimationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReleveCompteurRepository $releveCompteurRepository,
        private readonly FacturationPeriodeRepository $facturationPeriodeRepository
    ) {
    }

    /**
     * Calcule l'estimation pour une ligne de contrat
     * 
     * @param ContratLigne $contratLigne
     * @return array ['compteursActuels' => array, 'derniereFacture' => array|null, 'estimation' => array]
     */
    public function calculateEstimation(ContratLigne $contratLigne): array
    {
        // Récupérer la dernière période facturée ou validée
        $dernierePeriode = $this->facturationPeriodeRepository->createQueryBuilder('fp')
            ->where('fp.contratLigne = :ligne')
            ->andWhere('fp.statut IN (:statuts)')
            ->setParameter('ligne', $contratLigne)
            ->setParameter('statuts', [\App\Enum\StatutFacturation::FACTURE, \App\Enum\StatutFacturation::VALIDE])
            ->orderBy('fp.dateFin', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Récupérer les compteurs actuels pour chaque imprimante affectée
        $compteursActuels = [];
        $affectationsActives = $contratLigne->getAffectationsMateriel()->filter(function($aff) {
            return $aff->getDateFin() === null;
        });

        foreach ($affectationsActives as $affectation) {
            $imprimante = $affectation->getImprimante();
            $dernierReleve = $this->releveCompteurRepository->createQueryBuilder('r')
                ->where('r.imprimante = :imprimante')
                ->orderBy('r.dateReleve', 'DESC')
                ->setMaxResults(1)
                ->setParameter('imprimante', $imprimante)
                ->getQuery()
                ->getOneOrNullResult();

            if ($dernierReleve) {
                $compteursActuels[] = [
                    'imprimante' => $imprimante,
                    'releve' => $dernierReleve,
                    'compteurNoir' => $dernierReleve->getCompteurNoir() ?? 0,
                    'compteurCouleur' => $dernierReleve->getCompteurCouleur(),
                    'dateReleve' => $dernierReleve->getDateReleve(),
                ];
            }
        }

        // Calculer l'estimation pour la prochaine facturation
        $estimation = $this->calculateEstimationForNextBilling($contratLigne, $dernierePeriode, $compteursActuels);

        // Préparer les données de la dernière facture
        $derniereFacture = null;
        if ($dernierePeriode) {
            $compteursDerniereFacture = [];
            foreach ($dernierePeriode->getFacturationCompteurs() as $compteur) {
                $affectation = $compteur->getAffectationMateriel();
                $imprimante = $affectation->getImprimante();
                
                $compteursDerniereFacture[] = [
                    'imprimante' => $imprimante,
                    'compteurDebutNoir' => $compteur->getCompteurDebutNoir(),
                    'compteurFinNoir' => $compteur->getCompteurFinNoir(),
                    'compteurDebutCouleur' => $compteur->getCompteurDebutCouleur(),
                    'compteurFinCouleur' => $compteur->getCompteurFinCouleur(),
                    'pagesNoir' => $compteur->getPagesNoir(),
                    'pagesCouleur' => $compteur->getPagesCouleur(),
                    'compteurFinEstime' => $compteur->isCompteurFinEstime(),
                    'dateReleveFin' => $compteur->getDateReleveFin(),
                ];
            }

            $derniereFacture = [
                'periode' => $dernierePeriode,
                'compteurs' => $compteursDerniereFacture,
            ];
        }

        return [
            'compteursActuels' => $compteursActuels,
            'derniereFacture' => $derniereFacture,
            'estimation' => $estimation,
        ];
    }

    /**
     * Calcule l'estimation pour la prochaine facturation
     */
    private function calculateEstimationForNextBilling(
        ContratLigne $contratLigne,
        ?\App\Entity\FacturationPeriode $dernierePeriode,
        array $compteursActuels
    ): array {
        $prochaineFacturation = $contratLigne->getProchaineFacturation();
        $aujourdhui = new \DateTimeImmutable();
        $diff = $aujourdhui->diff($prochaineFacturation);
        $joursRestants = $prochaineFacturation > $aujourdhui ? $diff->days : 0;

        $estimations = [];

        foreach ($compteursActuels as $compteurActuel) {
            $imprimante = $compteurActuel['imprimante'];
            $compteurNoirActuel = $compteurActuel['compteurNoir'];
            $compteurCouleurActuel = $compteurActuel['compteurCouleur'] ?? 0;
            $dateReleveActuel = $compteurActuel['dateReleve'];

            // Trouver les compteurs de la dernière facture pour cette imprimante
            $compteurDebutNoir = $compteurNoirActuel;
            $compteurDebutCouleur = $compteurCouleurActuel;

            if ($dernierePeriode) {
                foreach ($dernierePeriode->getFacturationCompteurs() as $compteurFacture) {
                    if ($compteurFacture->getAffectationMateriel()->getImprimante()->getId() === $imprimante->getId()) {
                        $compteurDebutNoir = $compteurFacture->getCompteurFinNoir();
                        $compteurDebutCouleur = $compteurFacture->getCompteurFinCouleur() ?? 0;
                        break;
                    }
                }
            }

            // Calculer la consommation moyenne journalière depuis la dernière facture
            $joursDepuisDerniereFacture = 0;
            $pagesNoirConsommees = 0;
            $pagesCouleurConsommees = 0;

            if ($dernierePeriode) {
                $dateFinDerniereFacture = $dernierePeriode->getDateFin();
                // Calculer les jours depuis la fin de la dernière facture jusqu'au relevé actuel
                $joursDepuisDerniereFacture = abs($dateReleveActuel->diff($dateFinDerniereFacture)->days);
                
                if ($joursDepuisDerniereFacture > 0 && $dateReleveActuel > $dateFinDerniereFacture) {
                    $pagesNoirConsommees = max(0, $compteurNoirActuel - $compteurDebutNoir);
                    $pagesCouleurConsommees = max(0, $compteurCouleurActuel - $compteurDebutCouleur);
                } else {
                    // Si le relevé est avant la fin de la dernière facture, utiliser 0
                    $joursDepuisDerniereFacture = 0;
                }
            } else {
                // Si pas de facture précédente, utiliser les relevés des 30 derniers jours
                $dateLimite = $aujourdhui->modify('-30 days');
                $releves = $this->releveCompteurRepository->createQueryBuilder('r')
                    ->where('r.imprimante = :imprimante')
                    ->andWhere('r.dateReleve >= :dateLimite')
                    ->setParameter('imprimante', $imprimante)
                    ->setParameter('dateLimite', $dateLimite)
                    ->orderBy('r.dateReleve', 'ASC')
                    ->getQuery()
                    ->getResult();

                if (count($releves) >= 2) {
                    $premierReleve = $releves[0];
                    $dernierReleve = $releves[count($releves) - 1];
                    $joursDepuisDerniereFacture = abs($dernierReleve->getDateReleve()->diff($premierReleve->getDateReleve())->days);
                    
                    if ($joursDepuisDerniereFacture > 0) {
                        $pagesNoirConsommees = max(0, ($dernierReleve->getCompteurNoir() ?? 0) - ($premierReleve->getCompteurNoir() ?? 0));
                        $pagesCouleurConsommees = max(0, ($dernierReleve->getCompteurCouleur() ?? 0) - ($premierReleve->getCompteurCouleur() ?? 0));
                        // Utiliser le compteur de début comme le premier relevé
                        $compteurDebutNoir = $premierReleve->getCompteurNoir() ?? 0;
                        $compteurDebutCouleur = $premierReleve->getCompteurCouleur() ?? 0;
                    }
                }
            }

            // Calculer la consommation moyenne journalière
            $consommationMoyenneJournaliereNoir = $joursDepuisDerniereFacture > 0 
                ? ($pagesNoirConsommees / $joursDepuisDerniereFacture) 
                : 0;
            $consommationMoyenneJournaliereCouleur = $joursDepuisDerniereFacture > 0 
                ? ($pagesCouleurConsommees / $joursDepuisDerniereFacture) 
                : 0;

            // Estimer les compteurs à la date de prochaine facturation
            $compteurEstimeNoir = $compteurNoirActuel + (int) round($consommationMoyenneJournaliereNoir * $joursRestants);
            $compteurEstimeCouleur = $compteurCouleurActuel + (int) round($consommationMoyenneJournaliereCouleur * $joursRestants);

            // Calculer les pages estimées
            $pagesEstimeesNoir = max(0, $compteurEstimeNoir - $compteurDebutNoir);
            $pagesEstimeesCouleur = max(0, $compteurEstimeCouleur - $compteurDebutCouleur);

            $estimations[] = [
                'imprimante' => $imprimante,
                'compteurDebutNoir' => $compteurDebutNoir,
                'compteurActuelNoir' => $compteurNoirActuel,
                'compteurEstimeNoir' => $compteurEstimeNoir,
                'compteurDebutCouleur' => $compteurDebutCouleur,
                'compteurActuelCouleur' => $compteurCouleurActuel,
                'compteurEstimeCouleur' => $compteurEstimeCouleur,
                'pagesEstimeesNoir' => $pagesEstimeesNoir,
                'pagesEstimeesCouleur' => $pagesEstimeesCouleur,
                'consommationMoyenneJournaliereNoir' => round($consommationMoyenneJournaliereNoir, 2),
                'consommationMoyenneJournaliereCouleur' => round($consommationMoyenneJournaliereCouleur, 2),
                'joursRestants' => $joursRestants,
                'dateReleveActuel' => $dateReleveActuel,
                'joursDepuisDerniereFacture' => $joursDepuisDerniereFacture,
            ];
        }

        // Calculer le montant estimé
        $montantEstime = (float) ($contratLigne->getPrixFixe() ?? 0);
        $pagesNoirTotal = 0;
        $pagesCouleurTotal = 0;

        foreach ($estimations as $estimation) {
            $pagesNoirTotal += $estimation['pagesEstimeesNoir'];
            $pagesCouleurTotal += $estimation['pagesEstimeesCouleur'];

            if ($estimation['pagesEstimeesNoir'] > 0 && $contratLigne->getPrixPageNoir() !== null) {
                $pagesInclusesNoir = $contratLigne->getPagesInclusesNoir() ?? 0;
                $pagesFacturablesNoir = max(0, $estimation['pagesEstimeesNoir'] - $pagesInclusesNoir);
                if ($pagesFacturablesNoir > 0) {
                    $montantEstime += $pagesFacturablesNoir * (float) $contratLigne->getPrixPageNoir();
                }
            }

            if ($estimation['pagesEstimeesCouleur'] > 0 && $contratLigne->getPrixPageCouleur() !== null) {
                $pagesInclusesCouleur = $contratLigne->getPagesInclusesCouleur() ?? 0;
                $pagesFacturablesCouleur = max(0, $estimation['pagesEstimeesCouleur'] - $pagesInclusesCouleur);
                if ($pagesFacturablesCouleur > 0) {
                    $montantEstime += $pagesFacturablesCouleur * (float) $contratLigne->getPrixPageCouleur();
                }
            }
        }

        return [
            'estimations' => $estimations,
            'montantEstime' => round($montantEstime, 2),
            'pagesNoirTotal' => $pagesNoirTotal,
            'pagesCouleurTotal' => $pagesCouleurTotal,
            'prochaineFacturation' => $prochaineFacturation,
        ];
    }
}
