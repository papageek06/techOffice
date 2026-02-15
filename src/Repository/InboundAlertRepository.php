<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\InboundAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InboundAlert>
 */
class InboundAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboundAlert::class);
    }

    public function findByMessageId(string $messageId): ?InboundAlert
    {
        return $this->findOneBy(['messageId' => $messageId], ['id' => 'DESC']);
    }
}
