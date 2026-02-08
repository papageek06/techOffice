<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Imprimante;
use App\Entity\PrinterExternalRef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrinterExternalRef>
 */
class PrinterExternalRefRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrinterExternalRef::class);
    }

    public function findByProviderAndExternalId(string $provider, string $externalId): ?PrinterExternalRef
    {
        return $this->findOneBy(
            ['provider' => $provider, 'externalId' => $externalId],
            ['id' => 'DESC']
        );
    }
}
