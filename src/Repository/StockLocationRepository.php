<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\StockLocation;
use App\Enum\StockLocationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockLocation>
 */
class StockLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockLocation::class);
    }

    /**
     * Trouve le stock CLIENT d'un site
     */
    public function findClientStockForSite(Site $site): ?StockLocation
    {
        return $this->createQueryBuilder('sl')
            ->where('sl.site = :site')
            ->andWhere('sl.type = :type')
            ->andWhere('sl.actif = true')
            ->setParameter('site', $site)
            ->setParameter('type', StockLocationType::CLIENT)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve tous les stocks ENTREPRISE actifs
     */
    public function findEntrepriseStocks(): array
    {
        return $this->createQueryBuilder('sl')
            ->where('sl.type = :type')
            ->andWhere('sl.actif = true')
            ->setParameter('type', StockLocationType::ENTREPRISE)
            ->orderBy('sl.nomStock', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
