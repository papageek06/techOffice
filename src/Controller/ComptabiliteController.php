<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\FacturationPeriode;
use App\Enum\StatutFacturation;
use App\Repository\ContratRepository;
use App\Repository\FacturationPeriodeRepository;
use App\Service\BillingCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Contrôleur pour la section Comptabilité
 * Accessible uniquement aux rôles ROLE_COMPTABLE et ROLE_ADMIN
 */
#[Route('/comptabilite')]
final class ComptabiliteController extends AbstractController
{
    #[Route('', name: 'app_comptabilite_dashboard', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function dashboard(
        EntityManagerInterface $entityManager,
        ContratRepository $contratRepository,
        FacturationPeriodeRepository $facturationPeriodeRepository,
        BillingCalculator $billingCalculator
    ): Response {
        // Statistiques globales
        $stats = [
            'contrats_actifs' => count($contratRepository->findActifs()),
            'periodes_brouillon' => count($facturationPeriodeRepository->findBy(['statut' => StatutFacturation::BROUILLON])),
            'periodes_validees' => count($facturationPeriodeRepository->findBy(['statut' => StatutFacturation::VALIDE])),
            'periodes_facturees' => count($facturationPeriodeRepository->findBy(['statut' => StatutFacturation::FACTURE])),
        ];

        // Périodes en brouillon (à valider)
        $periodesBrouillon = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->join('cl.site', 's')
            ->where('fp.statut = :statut')
            ->setParameter('statut', StatutFacturation::BROUILLON)
            ->orderBy('fp.dateDebut', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Calculer les montants pour chaque période en brouillon
        $periodesAvecMontants = [];
        foreach ($periodesBrouillon as $periode) {
            $calcul = $billingCalculator->calculateForPeriod($periode);
            $periodesAvecMontants[] = [
                'periode' => $periode,
                'montant' => $calcul['montant'],
                'pagesNoir' => $calcul['pagesNoir'],
                'pagesCouleur' => $calcul['pagesCouleur'],
            ];
        }

        // Périodes validées récentes
        $periodesValidees = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->join('cl.site', 's')
            ->where('fp.statut = :statut')
            ->setParameter('statut', StatutFacturation::VALIDE)
            ->orderBy('fp.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Montant total des périodes validées
        $montantTotalValide = 0;
        foreach ($periodesValidees as $periode) {
            $calcul = $billingCalculator->calculateForPeriod($periode);
            $montantTotalValide += $calcul['montant'];
        }

        return $this->render('comptabilite/dashboard.html.twig', [
            'stats' => $stats,
            'periodesAvecMontants' => $periodesAvecMontants,
            'periodesValidees' => $periodesValidees,
            'montantTotalValide' => round($montantTotalValide, 2),
        ]);
    }

    #[Route('/contrats', name: 'app_comptabilite_contrats', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function contrats(ContratRepository $contratRepository): Response
    {
        $contrats = $contratRepository->findAll();

        return $this->render('comptabilite/contrats.html.twig', [
            'contrats' => $contrats,
        ]);
    }

    #[Route('/contrats/{id}', name: 'app_comptabilite_contrat_show', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function contratShow(Contrat $contrat, BillingCalculator $billingCalculator): Response
    {
        // Préparer les données de facturation pour chaque ligne de contrat
        $lignesAvecFacturation = [];
        foreach ($contrat->getContratLignes() as $ligne) {
            $periodesAvecMontants = [];
            foreach ($ligne->getFacturationPeriodes() as $periode) {
                $calcul = $billingCalculator->calculateForPeriod($periode);
                $periodesAvecMontants[] = [
                    'periode' => $periode,
                    'montant' => $calcul['montant'],
                    'pagesNoir' => $calcul['pagesNoir'],
                    'pagesCouleur' => $calcul['pagesCouleur'],
                    'details' => $calcul['details'],
                ];
            }
            
            $lignesAvecFacturation[] = [
                'ligne' => $ligne,
                'periodes' => $periodesAvecMontants,
            ];
        }

        return $this->render('contrat/show.html.twig', [
            'contrat' => $contrat,
            'lignesAvecFacturation' => $lignesAvecFacturation,
        ]);
    }

    #[Route('/periodes', name: 'app_comptabilite_periodes', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function periodes(
        FacturationPeriodeRepository $facturationPeriodeRepository,
        BillingCalculator $billingCalculator
    ): Response {
        $periodes = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->join('cl.site', 's')
            ->orderBy('fp.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();

        // Calculer les montants pour chaque période
        $periodesAvecMontants = [];
        foreach ($periodes as $periode) {
            $calcul = $billingCalculator->calculateForPeriod($periode);
            $periodesAvecMontants[] = [
                'periode' => $periode,
                'montant' => $calcul['montant'],
                'pagesNoir' => $calcul['pagesNoir'],
                'pagesCouleur' => $calcul['pagesCouleur'],
                'details' => $calcul['details'],
            ];
        }

        return $this->render('comptabilite/periodes.html.twig', [
            'periodesAvecMontants' => $periodesAvecMontants,
        ]);
    }

    #[Route('/periode/{id}', name: 'app_comptabilite_periode_show', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function periodeShow(
        FacturationPeriode $facturationPeriode,
        BillingCalculator $billingCalculator
    ): Response {
        $calcul = $billingCalculator->calculateForPeriod($facturationPeriode);

        return $this->render('comptabilite/periode_show.html.twig', [
            'periode' => $facturationPeriode,
            'calcul' => $calcul,
        ]);
    }

    /**
     * Route AJAX pour récupérer les détails d'une période avec les compteurs des imprimantes
     */
    #[Route('/periode/{id}/details-ajax', name: 'app_comptabilite_periode_details_ajax', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function periodeDetailsAjax(
        FacturationPeriode $facturationPeriode,
        BillingCalculator $billingCalculator
    ): JsonResponse {
        $calcul = $billingCalculator->calculateForPeriod($facturationPeriode);
        $contratLigne = $facturationPeriode->getContratLigne();

        // Récupérer les compteurs de facturation avec les imprimantes
        $compteursDetails = [];
        foreach ($facturationPeriode->getFacturationCompteurs() as $compteur) {
            $affectation = $compteur->getAffectationMateriel();
            $imprimante = $affectation->getImprimante();
            
            $compteursDetails[] = [
                'imprimante' => [
                    'id' => $imprimante->getId(),
                    'numeroSerie' => $imprimante->getNumeroSerie(),
                    'modele' => $imprimante->getModele()->getReferenceModele(),
                    'adresseIp' => $imprimante->getAdresseIp(),
                ],
                'compteurs' => [
                    'debutNoir' => $compteur->getCompteurDebutNoir(),
                    'finNoir' => $compteur->getCompteurFinNoir(),
                    'pagesNoir' => $compteur->getPagesNoir(),
                    'debutCouleur' => $compteur->getCompteurDebutCouleur(),
                    'finCouleur' => $compteur->getCompteurFinCouleur(),
                    'pagesCouleur' => $compteur->getPagesCouleur(),
                    'sourceDebut' => $compteur->getSourceDebut()->value,
                    'sourceFin' => $compteur->getSourceFin()->value,
                    'compteurFinEstime' => $compteur->isCompteurFinEstime(),
                    'dateReleveFin' => $compteur->getDateReleveFin()?->format('d/m/Y'),
                ],
                'affectation' => [
                    'dateDebut' => $affectation->getDateDebut()->format('d/m/Y H:i'),
                    'dateFin' => $affectation->getDateFin()?->format('d/m/Y H:i'),
                    'type' => $affectation->getTypeAffectation()->value,
                ],
            ];
        }

        return new JsonResponse([
            'success' => true,
            'periode' => [
                'id' => $facturationPeriode->getId(),
                'dateDebut' => $facturationPeriode->getDateDebut()->format('d/m/Y'),
                'dateFin' => $facturationPeriode->getDateFin()->format('d/m/Y'),
                'statut' => $facturationPeriode->getStatut()->value,
            ],
            'calcul' => [
                'montant' => $calcul['montant'],
                'pagesNoir' => $calcul['pagesNoir'],
                'pagesCouleur' => $calcul['pagesCouleur'],
                'details' => $calcul['details'],
            ],
            'contratLigne' => [
                'id' => $contratLigne->getId(),
                'libelle' => $contratLigne->getLibelle(),
                'prixFixe' => $contratLigne->getPrixFixe(),
                'prixPageNoir' => $contratLigne->getPrixPageNoir(),
                'prixPageCouleur' => $contratLigne->getPrixPageCouleur(),
                'pagesInclusesNoir' => $contratLigne->getPagesInclusesNoir(),
                'pagesInclusesCouleur' => $contratLigne->getPagesInclusesCouleur(),
            ],
            'compteurs' => $compteursDetails,
        ]);
    }
}
