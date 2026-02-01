<?php

namespace App\Service;

use App\Entity\AffectationMateriel;
use App\Entity\FacturationCompteur;
use App\Entity\FacturationPeriode;
use App\Entity\ReleveCompteur;
use App\Enum\SourceCompteur;
use App\Repository\AffectationMaterielRepository;
use App\Repository\ReleveCompteurRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour résoudre les snapshots de compteurs pour une période de facturation
 */
class CounterSnapshotResolver
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AffectationMaterielRepository $affectationMaterielRepository,
        private readonly ReleveCompteurRepository $releveCompteurRepository
    ) {
    }

    /**
     * Résout et crée les FacturationCompteur pour une période de facturation
     * 
     * @param FacturationPeriode $periode
     * @return array Liste des FacturationCompteur créés
     */
    public function resolveForPeriod(FacturationPeriode $periode): array
    {
        $contratLigne = $periode->getContratLigne();
        $dateDebut = $periode->getDateDebut();
        $dateFin = $periode->getDateFin();

        // Trouver toutes les affectations actives dans cette période
        $affectations = $this->affectationMaterielRepository->findForPeriod(
            $contratLigne,
            $dateDebut,
            $dateFin
        );

        $facturationCompteurs = [];

        foreach ($affectations as $affectation) {
            $compteur = $this->resolveForAffectation($affectation, $dateDebut, $dateFin);
            if ($compteur !== null) {
                $compteur->setFacturationPeriode($periode);
                $this->entityManager->persist($compteur);
                $facturationCompteurs[] = $compteur;
            }
        }

        $this->entityManager->flush();

        return $facturationCompteurs;
    }

    /**
     * Résout les compteurs pour une affectation dans une période
     */
    private function resolveForAffectation(
        AffectationMateriel $affectation,
        \DateTimeImmutable $dateDebut,
        \DateTimeImmutable $dateFin
    ): ?FacturationCompteur {
        $imprimante = $affectation->getImprimante();

        // Ajuster les dates selon l'affectation
        $dateDebutEffective = max($affectation->getDateDebut(), $dateDebut);
        $dateFinEffective = $affectation->getDateFin() !== null 
            ? min($affectation->getDateFin(), $dateFin) 
            : $dateFin;

        // Trouver le relevé de début (le plus proche avant ou à la date de début)
        $releveDebut = $this->findReleveForDate($imprimante, $dateDebutEffective, true);
        if ($releveDebut === null) {
            // Pas de relevé trouvé, on ne peut pas créer de facturation
            return null;
        }

        // Trouver le relevé de fin (le plus proche avant ou à la date de fin)
        $releveFin = $this->findReleveForDate($imprimante, $dateFinEffective, true);
        
        $compteurFinEstime = false;
        $dateReleveFin = null;
        
        if ($releveFin === null || $releveFin->getId() === $releveDebut->getId()) {
            // Pas de relevé de fin ou même relevé
            // Vérifier si on peut estimer à partir d'un relevé proche (dans les 5 jours après)
            $releveApres = $this->findReleveForDate($imprimante, $dateFinEffective, false);
            
            if ($releveApres && $releveApres->getId() !== $releveDebut->getId()) {
                // Vérifier si le relevé est à 5 jours près
                $joursDifference = abs($releveApres->getDateReleve()->diff($dateFinEffective)->days);
                if ($joursDifference <= 5) {
                    // Utiliser le relevé réel (proche de la date de fin)
                    $releveFin = $releveApres;
                    $compteurFinEstime = false;
                    $dateReleveFin = $releveFin->getDateReleve();
                } else {
                    // Trop éloigné, estimer à partir du dernier relevé disponible
                    $releveFin = $releveDebut;
                    $compteurFinEstime = true;
                    // Estimer basé sur la consommation moyenne
                    $compteurFinNoir = $this->estimateCounter($releveDebut, $dateFinEffective, 'noir');
                    $compteurFinCouleur = $this->estimateCounter($releveDebut, $dateFinEffective, 'couleur');
                }
            } else {
                // Pas de relevé après, utiliser le relevé de début et estimer
                $releveFin = $releveDebut;
                $compteurFinEstime = true;
                $compteurFinNoir = $this->estimateCounter($releveDebut, $dateFinEffective, 'noir');
                $compteurFinCouleur = $this->estimateCounter($releveDebut, $dateFinEffective, 'couleur');
            }
        } else {
            // Vérifier si le relevé de fin est à 5 jours près
            $joursDifference = abs($releveFin->getDateReleve()->diff($dateFinEffective)->days);
            if ($joursDifference > 5) {
                // Trop éloigné, marquer comme estimé mais utiliser quand même le relevé
                $compteurFinEstime = true;
            }
            $dateReleveFin = $releveFin->getDateReleve();
        }

        // Créer le FacturationCompteur
        $facturationCompteur = new FacturationCompteur();
        $facturationCompteur->setAffectationMateriel($affectation);
        $facturationCompteur->setCompteurDebutNoir($releveDebut->getCompteurNoir() ?? 0);
        
        if (isset($compteurFinNoir)) {
            $facturationCompteur->setCompteurFinNoir($compteurFinNoir);
        } else {
            $facturationCompteur->setCompteurFinNoir($releveFin->getCompteurNoir() ?? 0);
        }
        
        $facturationCompteur->setCompteurDebutCouleur($releveDebut->getCompteurCouleur());
        
        if (isset($compteurFinCouleur)) {
            $facturationCompteur->setCompteurFinCouleur($compteurFinCouleur);
        } else {
            $facturationCompteur->setCompteurFinCouleur($releveFin->getCompteurCouleur());
        }
        
        $facturationCompteur->setSourceDebut($this->mapSourceToEnum($releveDebut->getSource()));
        $facturationCompteur->setSourceFin($this->mapSourceToEnum($releveFin->getSource()));
        $facturationCompteur->setCompteurFinEstime($compteurFinEstime);
        $facturationCompteur->setDateReleveFin($dateReleveFin);

        return $facturationCompteur;
    }

    /**
     * Trouve le relevé de compteur le plus proche d'une date
     * 
     * @param \App\Entity\Imprimante $imprimante
     * @param \DateTimeImmutable $date
     * @param bool $before Si true, cherche avant ou à la date, sinon après ou à la date
     * @return ReleveCompteur|null
     */
    private function findReleveForDate(\App\Entity\Imprimante $imprimante, \DateTimeImmutable $date, bool $before = true): ?ReleveCompteur
    {
        $qb = $this->releveCompteurRepository->createQueryBuilder('r')
            ->where('r.imprimante = :imprimante')
            ->setParameter('imprimante', $imprimante);

        if ($before) {
            $qb->andWhere('r.dateReleve <= :date')
               ->orderBy('r.dateReleve', 'DESC');
        } else {
            $qb->andWhere('r.dateReleve >= :date')
               ->orderBy('r.dateReleve', 'ASC');
        }

        $qb->setParameter('date', $date)
           ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Mappe la source string vers l'enum SourceCompteur
     */
    private function mapSourceToEnum(string $source): SourceCompteur
    {
        return match (strtolower($source)) {
            'snmp' => SourceCompteur::SNMP,
            'scan' => SourceCompteur::SCAN,
            default => SourceCompteur::MANUEL,
        };
    }

    /**
     * Estime un compteur basé sur la consommation moyenne depuis le dernier relevé
     */
    private function estimateCounter(ReleveCompteur $dernierReleve, \DateTimeImmutable $dateCible, string $type): int
    {
        $dateReleve = $dernierReleve->getDateReleve();
        $jours = abs($dateCible->diff($dateReleve)->days);
        
        if ($jours === 0) {
            return $type === 'noir' 
                ? ($dernierReleve->getCompteurNoir() ?? 0)
                : ($dernierReleve->getCompteurCouleur() ?? 0);
        }

        // Consommation moyenne estimée : 100 pages/jour pour noir, 30 pour couleur
        $consommationMoyenne = $type === 'noir' ? 100 : 30;
        $estimation = $consommationMoyenne * $jours;

        $compteurBase = $type === 'noir' 
            ? ($dernierReleve->getCompteurNoir() ?? 0)
            : ($dernierReleve->getCompteurCouleur() ?? 0);

        return $compteurBase + $estimation;
    }
}
