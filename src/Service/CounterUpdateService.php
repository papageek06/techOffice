<?php

namespace App\Service;

use App\Entity\FacturationCompteur;
use App\Entity\FacturationPeriode;
use App\Entity\ReleveCompteur;
use App\Enum\SourceCompteur;
use App\Repository\FacturationCompteurRepository;
use App\Repository\FacturationPeriodeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour mettre à jour les compteurs de facturation quand on reçoit de nouveaux relevés
 */
class CounterUpdateService
{
    private const TOLERANCE_JOURS = 5; // Tolérance de 5 jours pour accepter un relevé

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FacturationCompteurRepository $facturationCompteurRepository,
        private readonly FacturationPeriodeRepository $facturationPeriodeRepository
    ) {
    }

    /**
     * Met à jour les compteurs de facturation pour une imprimante après réception d'un nouveau relevé
     * 
     * @param ReleveCompteur $nouveauReleve Le nouveau relevé reçu
     * @return array Liste des compteurs mis à jour
     */
    public function updateCountersForReleve(ReleveCompteur $nouveauReleve): array
    {
        $imprimante = $nouveauReleve->getImprimante();
        $dateReleve = $nouveauReleve->getDateReleve();
        $compteursMisAJour = [];

        // Trouver toutes les périodes de facturation qui pourraient être affectées
        // (périodes en brouillon ou validées, dont la date de fin est proche de la date du relevé)
        $dateLimiteDebut = $dateReleve->modify('-' . (self::TOLERANCE_JOURS + 30) . ' days');
        $dateLimiteFin = $dateReleve->modify('+' . self::TOLERANCE_JOURS . ' days');

        $periodes = $this->facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.affectationsMateriel', 'am')
            ->where('am.imprimante = :imprimante')
            ->andWhere('fp.dateFin >= :dateLimiteDebut')
            ->andWhere('fp.dateFin <= :dateLimiteFin')
            ->andWhere('fp.statut IN (:statuts)')
            ->setParameter('imprimante', $imprimante)
            ->setParameter('dateLimiteDebut', $dateLimiteDebut)
            ->setParameter('dateLimiteFin', $dateLimiteFin)
            ->setParameter('statuts', [\App\Enum\StatutFacturation::BROUILLON, \App\Enum\StatutFacturation::VALIDE])
            ->getQuery()
            ->getResult();

        foreach ($periodes as $periode) {
            // Trouver le compteur de facturation pour cette période et cette imprimante
            $affectation = null;
            foreach ($periode->getContratLigne()->getAffectationsMateriel() as $aff) {
                if ($aff->getImprimante()->getId() === $imprimante->getId() &&
                    $aff->getDateDebut() <= $periode->getDateFin() &&
                    ($aff->getDateFin() === null || $aff->getDateFin() >= $periode->getDateDebut())) {
                    $affectation = $aff;
                    break;
                }
            }

            if (!$affectation) {
                continue;
            }

            $compteur = $this->facturationCompteurRepository->findOneBy([
                'facturationPeriode' => $periode,
                'affectationMateriel' => $affectation,
            ]);

            if (!$compteur) {
                continue;
            }

            // Vérifier si la date du relevé est à 5 jours près de la date de fin de période
            $joursDifference = abs($dateReleve->diff($periode->getDateFin())->days);

            if ($joursDifference <= self::TOLERANCE_JOURS) {
                // Le relevé est proche de la date de fin, on peut l'utiliser
                // MAIS : le compteur de début doit rester le même (celui de fin de la période précédente)
                // Seul le compteur de fin peut être mis à jour

                // Vérifier si le compteur de fin était estimé ou si le nouveau relevé est plus récent
                if ($compteur->isCompteurFinEstime() || 
                    ($compteur->getDateReleveFin() === null || $dateReleve > $compteur->getDateReleveFin())) {
                    
                    // Mettre à jour le compteur de fin avec le relevé réel
                    $compteur->setCompteurFinNoir($nouveauReleve->getCompteurNoir() ?? 0);
                    $compteur->setCompteurFinCouleur($nouveauReleve->getCompteurCouleur());
                    $compteur->setCompteurFinEstime(false);
                    $compteur->setDateReleveFin($dateReleve);
                    $compteur->setSourceFin($this->mapSourceToEnum($nouveauReleve->getSource()));

                    $this->entityManager->persist($compteur);
                    $compteursMisAJour[] = $compteur;
                }
            } else {
                // Le relevé n'est pas assez proche, mais on peut l'utiliser pour estimer
                // si le compteur de fin était estimé et qu'on n'a pas de relevé plus proche
                if ($compteur->isCompteurFinEstime() && 
                    ($compteur->getDateReleveFin() === null || $dateReleve > $compteur->getDateReleveFin())) {
                    
                    // Utiliser ce relevé pour l'estimation (mais marquer comme estimé)
                    $compteur->setCompteurFinNoir($nouveauReleve->getCompteurNoir() ?? 0);
                    $compteur->setCompteurFinCouleur($nouveauReleve->getCompteurCouleur());
                    $compteur->setCompteurFinEstime(true);
                    $compteur->setDateReleveFin($dateReleve);
                    $compteur->setSourceFin($this->mapSourceToEnum($nouveauReleve->getSource()));

                    $this->entityManager->persist($compteur);
                    $compteursMisAJour[] = $compteur;
                }
            }
        }

        // Mettre à jour le compteur de début de la période suivante pour chaque compteur mis à jour
        // IMPORTANT : Le compteur de début de la période suivante = compteur de fin de la période actuelle
        foreach ($compteursMisAJour as $compteurMisAJour) {
            $this->updateNextPeriodStartCounter($compteurMisAJour);
        }

        if (!empty($compteursMisAJour)) {
            $this->entityManager->flush();
        }

        return $compteursMisAJour;
    }

    /**
     * Met à jour le compteur de début de la période suivante avec le compteur de fin de la période actuelle
     * IMPORTANT : Le compteur de début de la période suivante doit TOUJOURS être le compteur de fin de la période précédente
     */
    private function updateNextPeriodStartCounter(FacturationCompteur $compteur): void
    {
        $periode = $compteur->getFacturationPeriode();
        $imprimante = $compteur->getAffectationMateriel()->getImprimante();
        
        // Trouver la période suivante pour la même ligne de contrat
        $periodeSuivante = $this->facturationPeriodeRepository->createQueryBuilder('fp')
            ->where('fp.contratLigne = :ligne')
            ->andWhere('fp.dateDebut > :dateFin')
            ->setParameter('ligne', $periode->getContratLigne())
            ->setParameter('dateFin', $periode->getDateFin())
            ->orderBy('fp.dateDebut', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$periodeSuivante) {
            return; // Pas de période suivante
        }

        // Trouver l'affectation pour cette imprimante dans la période suivante
        $affectation = null;
        foreach ($periodeSuivante->getContratLigne()->getAffectationsMateriel() as $aff) {
            if ($aff->getImprimante()->getId() === $imprimante->getId() &&
                $aff->getDateDebut() <= $periodeSuivante->getDateFin() &&
                ($aff->getDateFin() === null || $aff->getDateFin() >= $periodeSuivante->getDateDebut())) {
                $affectation = $aff;
                break;
            }
        }

        if (!$affectation) {
            return; // Pas d'affectation pour cette imprimante dans la période suivante
        }

        // Trouver ou créer le compteur de la période suivante
        $compteurSuivant = $this->facturationCompteurRepository->findOneBy([
            'facturationPeriode' => $periodeSuivante,
            'affectationMateriel' => $affectation,
        ]);

        if ($compteurSuivant) {
            // Mettre à jour le compteur de début avec le compteur de fin de la période précédente
            // IMPORTANT : Le compteur de début doit toujours être le compteur de fin de la période précédente
            $compteurSuivant->setCompteurDebutNoir($compteur->getCompteurFinNoir());
            $compteurSuivant->setCompteurDebutCouleur($compteur->getCompteurFinCouleur());
            $compteurSuivant->setSourceDebut($compteur->getSourceFin());

            $this->entityManager->persist($compteurSuivant);
        }
    }

    /**
     * Mappe la source string vers l'enum SourceCompteur
     */
    private function mapSourceToEnum(string $source): SourceCompteur
    {
        return match (strtolower($source)) {
            'snmp' => SourceCompteur::SNMP,
            'scan', 'csv' => SourceCompteur::SCAN,
            default => SourceCompteur::MANUEL,
        };
    }
}
