<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\InboundAlertAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InboundAlertAttachment>
 */
class InboundAlertAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboundAlertAttachment::class);
    }

    public function findBySha256(string $sha256): ?InboundAlertAttachment
    {
        return $this->findOneBy(['sha256' => $sha256]);
    }
}
