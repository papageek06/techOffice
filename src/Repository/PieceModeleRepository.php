<?php

namespace App\Repository;

use App\Entity\Modele;
use App\Entity\Piece;
use App\Entity\PieceModele;
use App\Enum\PieceRoleModele;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PieceModele>
 */
class PieceModeleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PieceModele::class);
    }

    /**
     * Trouve la pièce correspondant à un modèle et un rôle
     */
    public function findPieceForModeleAndRole(Modele $modele, PieceRoleModele $role): ?Piece
    {
        $result = $this->createQueryBuilder('pm')
            ->select('p')
            ->join('pm.piece', 'p')
            ->where('pm.modele = :modele')
            ->andWhere('pm.role = :role')
            ->andWhere('p.actif = true')
            ->setParameter('modele', $modele)
            ->setParameter('role', $role)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Piece ? $result : null;
    }
}
