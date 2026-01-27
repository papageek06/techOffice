<?php

namespace App\Repository;

use App\Entity\FacturationPeriode;
use App\Entity\ContratLigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FacturationPeriode>
 */
class FacturationPeriodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacturationPeriode::class);
    }

    /**
     * Vérifie si une période existe déjà pour cette ligne et ces dates
     */
    public function existsForPeriod(ContratLigne $contratLigne, \DateTimeImmutable $dateDebut, \DateTimeImmutable $dateFin): bool
    {
        $count = $this->createQueryBuilder('fp')
            ->select('COUNT(fp.id)')
            ->where('fp.contratLigne = :contratLigne')
            ->andWhere('fp.dateDebut = :dateDebut')
            ->andWhere('fp.dateFin = :dateFin')
            ->setParameter('contratLigne', $contratLigne)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
