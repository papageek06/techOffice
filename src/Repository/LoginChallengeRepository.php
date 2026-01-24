<?php

namespace App\Repository;

use App\Entity\LoginChallenge;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginChallenge>
 */
class LoginChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginChallenge::class);
    }

    /**
     * Trouve un challenge actif pour un utilisateur et un deviceId
     */
    public function findActiveChallenge(User $user, string $deviceId): ?LoginChallenge
    {
        return $this->createQueryBuilder('lc')
            ->where('lc.user = :user')
            ->andWhere('lc.deviceId = :deviceId')
            ->andWhere('lc.expiresAt > :now')
            ->andWhere('lc.attempts < :maxAttempts')
            ->setParameter('user', $user)
            ->setParameter('deviceId', $deviceId)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('maxAttempts', 5)
            ->orderBy('lc.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Supprime les challenges expirés
     */
    public function removeExpiredChallenges(): int
    {
        return $this->createQueryBuilder('lc')
            ->delete()
            ->where('lc.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime tous les challenges pour un utilisateur et deviceId
     */
    public function removeChallengesForDevice(User $user, string $deviceId): int
    {
        return $this->createQueryBuilder('lc')
            ->delete()
            ->where('lc.user = :user')
            ->andWhere('lc.deviceId = :deviceId')
            ->setParameter('user', $user)
            ->setParameter('deviceId', $deviceId)
            ->getQuery()
            ->execute();
    }
}
