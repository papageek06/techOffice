<?php

namespace App\Repository;

use App\Entity\Imprimante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Imprimante>
 */
class ImprimanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Imprimante::class);
    }

    public function findOneByNumeroSerie(string $numeroSerie): ?Imprimante
    {
        return $this->findOneBy(['numeroSerie' => $numeroSerie], ['id' => 'DESC']);
    }

    public function findOneByAdresseIp(string $adresseIp): ?Imprimante
    {
        return $this->findOneBy(['adresseIp' => $adresseIp], ['id' => 'DESC']);
    }
}
