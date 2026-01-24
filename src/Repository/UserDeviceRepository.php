<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserDevice>
 */
class UserDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDevice::class);
    }

    /**
     * Trouve un appareil de confiance pour un utilisateur et un deviceId
     */
    public function findTrustedDevice(User $user, string $deviceId): ?UserDevice
    {
        return $this->createQueryBuilder('ud')
            ->where('ud.user = :user')
            ->andWhere('ud.deviceId = :deviceId')
            ->setParameter('user', $user)
            ->setParameter('deviceId', $deviceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve tous les appareils de confiance pour un utilisateur
     */
    public function findTrustedDevicesForUser(User $user): array
    {
        return $this->createQueryBuilder('ud')
            ->where('ud.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ud.lastUsedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les appareils expirés pour un utilisateur
     */
    public function removeExpiredDevices(User $user): int
    {
        return $this->createQueryBuilder('ud')
            ->delete()
            ->where('ud.user = :user')
            ->andWhere('ud.expiresAt IS NOT NULL')
            ->andWhere('ud.expiresAt < :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
