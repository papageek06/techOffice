<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Liste paginée avec recherche optionnelle par nom.
     * La recherche s'effectue sur l'ensemble des clients en base (requête SQL),
     * pas uniquement sur les clients de la page courante.
     *
     * @return array{items: list<Client>, total: int}
     */
    public function findPaginated(?string $q, int $page, int $perPage = 10): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC');

        if ($q !== null && $q !== '') {
            $qb->andWhere('c.nom LIKE :q')
                ->setParameter('q', '%' . trim($q) . '%');
        }

        $qb->select('c');
        $totalQb = clone $qb;
        $total = (int) $totalQb->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }
}
