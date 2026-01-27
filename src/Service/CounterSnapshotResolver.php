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
        $releveFin = $this->findReleveForDate($imprimante, $dateFinEffective, false);
        if ($releveFin === null || $releveFin->getId() === $releveDebut->getId()) {
            // Pas de relevé de fin ou même relevé, utiliser le relevé de début
            $releveFin = $releveDebut;
        }

        // Créer le FacturationCompteur
        $facturationCompteur = new FacturationCompteur();
        $facturationCompteur->setAffectationMateriel($affectation);
        $facturationCompteur->setCompteurDebutNoir($releveDebut->getCompteurNoir() ?? 0);
        $facturationCompteur->setCompteurFinNoir($releveFin->getCompteurNoir() ?? 0);
        $facturationCompteur->setCompteurDebutCouleur($releveDebut->getCompteurCouleur());
        $facturationCompteur->setCompteurFinCouleur($releveFin->getCompteurCouleur());
        $facturationCompteur->setSourceDebut($this->mapSourceToEnum($releveDebut->getSource()));
        $facturationCompteur->setSourceFin($this->mapSourceToEnum($releveFin->getSource()));

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
}
