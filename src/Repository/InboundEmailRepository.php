<?php

namespace App\Repository;

use App\Entity\InboundEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InboundEmail>
 */
class InboundEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboundEmail::class);
    }

    public function findByMessageId(string $messageId): ?InboundEmail
    {
        return $this->createQueryBuilder('e')
            ->where('e.messageId = :messageId')
            ->setParameter('messageId', $messageId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
