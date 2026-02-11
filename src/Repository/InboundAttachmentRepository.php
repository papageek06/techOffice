<?php

namespace App\Repository;

use App\Entity\InboundAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InboundAttachment>
 */
class InboundAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboundAttachment::class);
    }

    public function findBySha256(string $sha256): ?InboundAttachment
    {
        return $this->createQueryBuilder('a')
            ->where('a.sha256 = :sha256')
            ->setParameter('sha256', $sha256)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
