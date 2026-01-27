<?php

namespace App\Repository;

use App\Entity\ContratLigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContratLigne>
 */
class ContratLigneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratLigne::class);
    }

    /**
     * Trouve les lignes de contrat actives avec prochaine facturation <= date
     */
    public function findAvecFacturationDue(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('cl')
            ->where('cl.actif = :actif')
            ->andWhere('cl.prochaineFacturation <= :date')
            ->setParameter('actif', true)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
}
