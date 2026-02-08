<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\InboundEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InboundEvent>
 */
class InboundEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboundEvent::class);
    }

    public function findByFingerprint(string $fingerprint): ?InboundEvent
    {
        return $this->findOneBy(['fingerprint' => $fingerprint], ['id' => 'DESC']);
    }

    /**
     * @return InboundEvent[]
     */
    public function findByStatusAndProvider(?string $status, ?string $provider, int $limit = 500): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC');
        if ($status !== null && $status !== '') {
            $qb->andWhere('e.status = :status')->setParameter('status', $status);
        }
        if ($provider !== null && $provider !== '') {
            $qb->andWhere('e.provider = :provider')->setParameter('provider', $provider);
        }
        $qb->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}
