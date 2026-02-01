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

    /**
     * Trouve toutes les pièces compatibles avec un modèle, triées par rôle (toners en premier)
     */
    public function findPiecesForModele(Modele $modele): array
    {
        $results = $this->createQueryBuilder('pm')
            ->join('pm.piece', 'p')
            ->where('pm.modele = :modele')
            ->andWhere('p.actif = true')
            ->setParameter('modele', $modele)
            ->getQuery()
            ->getResult();

        // Trier manuellement pour mettre les toners en premier
        usort($results, function($a, $b) {
            $roleA = $a->getRole()->value;
            $roleB = $b->getRole()->value;
            
            // Les toners en premier
            $isTonerA = str_starts_with($roleA, 'TONER');
            $isTonerB = str_starts_with($roleB, 'TONER');
            
            if ($isTonerA && !$isTonerB) {
                return -1;
            }
            if (!$isTonerA && $isTonerB) {
                return 1;
            }
            
            // Si les deux sont des toners ou non-toners, trier par ordre alphabétique
            return strcmp($roleA, $roleB);
        });

        return $results;
    }
}
