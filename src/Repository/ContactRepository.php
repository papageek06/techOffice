<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public const SOURCE_M365 = 'm365';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function findForUserBySourceAndSourceId(User $user, string $source, string $sourceId): ?Contact
    {
        return $this->findOneBy(
            ['user' => $user, 'source' => $source, 'sourceId' => $sourceId]
        );
    }

    /** @return list<Contact> */
    public function findByUserAndSource(User $user, string $source, int $limit = 500): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.source = :source')
            ->setParameter('user', $user)
            ->setParameter('source', $source)
            ->orderBy('c.displayName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByUserAndSource(User $user, string $source): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.user = :user')
            ->andWhere('c.source = :source')
            ->setParameter('user', $user)
            ->setParameter('source', $source)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Contact $entity, bool $flush = false): void
    {
        $entity->setUpdatedAt(new \DateTimeImmutable());
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
