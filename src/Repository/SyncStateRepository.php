<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SyncState;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SyncState>
 */
class SyncStateRepository extends ServiceEntityRepository
{
    public const PROVIDER_M365_CONTACTS_SHARED = 'm365_contacts_shared';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SyncState::class);
    }

    public function findForUserAndProvider(User $user, string $provider): ?SyncState
    {
        return $this->findOneBy(
            ['user' => $user, 'provider' => $provider]
        );
    }

    public function getOrCreateForUserAndProvider(User $user, string $provider): SyncState
    {
        $state = $this->findForUserAndProvider($user, $provider);
        if ($state !== null) {
            return $state;
        }
        $state = new SyncState();
        $state->setUser($user);
        $state->setProvider($provider);
        $this->getEntityManager()->persist($state);
        $this->getEntityManager()->flush();
        return $state;
    }

    public function save(SyncState $entity, bool $flush = false): void
    {
        $entity->setUpdatedAt(new \DateTimeImmutable());
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
