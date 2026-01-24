<?php

namespace App\Entity;

use App\Repository\UserDeviceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un appareil de confiance pour un utilisateur
 * Permet de reconnaître les appareils déjà utilisés pour éviter l'OTP à chaque connexion
 */
#[ORM\Entity(repositoryClass: UserDeviceRepository::class)]
#[ORM\Table(name: 'user_device')]
#[ORM\UniqueConstraint(name: 'uniq_user_device', columns: ['user_id', 'device_id'])]
#[ORM\Index(name: 'idx_device_id', columns: ['device_id'])]
class UserDevice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    /**
     * UUID de l'appareil (stocké dans le cookie device_id)
     */
    #[ORM\Column(length: 36, unique: false)]
    private string $deviceId;

    /**
     * Nom/description de l'appareil (optionnel, pour l'affichage)
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviceName = null;

    /**
     * Date de création de l'enregistrement
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Date de dernière utilisation
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lastUsedAt;

    /**
     * Date d'expiration (null = confiance permanente)
     * Si définie, l'appareil expire après cette date
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    /**
     * Adresse IP lors de la première validation
     */
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    /**
     * User-Agent lors de la première validation
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $userAgent = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lastUsedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): self
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function setDeviceName(?string $deviceName): self
    {
        $this->deviceName = $deviceName;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLastUsedAt(): \DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(\DateTimeImmutable $lastUsedAt): self
    {
        $this->lastUsedAt = $lastUsedAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Vérifie si l'appareil est encore valide (non expiré)
     */
    public function isValid(): bool
    {
        if ($this->expiresAt === null) {
            return true; // Confiance permanente
        }

        return $this->expiresAt > new \DateTimeImmutable();
    }

    /**
     * Marque l'appareil comme utilisé maintenant
     */
    public function markAsUsed(): self
    {
        $this->lastUsedAt = new \DateTimeImmutable();
        return $this;
    }
}
