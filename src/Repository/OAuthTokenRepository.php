<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OAuthToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthToken>
 */
class OAuthTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuthToken::class);
    }

    public function findForUserAndProvider(User $user, string $provider): ?OAuthToken
    {
        return $this->findOneBy(
            ['user' => $user, 'provider' => $provider],
            ['updatedAt' => 'DESC']
        );
    }

    public function save(OAuthToken $entity, bool $flush = false): void
    {
        $entity->setUpdatedAt(new \DateTimeImmutable());
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
