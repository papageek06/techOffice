<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DefautImprimante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DefautImprimante>
 */
class DefautImprimanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DefautImprimante::class);
    }
}
