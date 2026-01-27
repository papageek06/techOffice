<?php

namespace App\Repository;

use App\Entity\AffectationMateriel;
use App\Entity\ContratLigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AffectationMateriel>
 */
class AffectationMaterielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffectationMateriel::class);
    }

    /**
     * Trouve l'affectation active pour une ligne de contrat
     */
    public function findActiveForContratLigne(ContratLigne $contratLigne): ?AffectationMateriel
    {
        return $this->createQueryBuilder('a')
            ->where('a.contratLigne = :contratLigne')
            ->andWhere('a.dateFin IS NULL')
            ->setParameter('contratLigne', $contratLigne)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve toutes les affectations actives pour une ligne de contrat dans une période
     */
    public function findForPeriod(ContratLigne $contratLigne, \DateTimeImmutable $dateDebut, \DateTimeImmutable $dateFin): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.contratLigne = :contratLigne')
            ->andWhere('a.dateDebut <= :dateFin')
            ->andWhere('(a.dateFin IS NULL OR a.dateFin >= :dateDebut)')
            ->setParameter('contratLigne', $contratLigne)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('a.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
