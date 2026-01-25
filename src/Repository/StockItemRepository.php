<?php

namespace App\Repository;

use App\Entity\Piece;
use App\Entity\StockItem;
use App\Entity\StockLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockItem>
 */
class StockItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockItem::class);
    }

    /**
     * Trouve un StockItem pour un stock et une pièce
     */
    public function findForStockAndPiece(StockLocation $stockLocation, Piece $piece): ?StockItem
    {
        return $this->createQueryBuilder('si')
            ->where('si.stockLocation = :stockLocation')
            ->andWhere('si.piece = :piece')
            ->setParameter('stockLocation', $stockLocation)
            ->setParameter('piece', $piece)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
