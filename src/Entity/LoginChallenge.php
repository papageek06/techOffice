<?php

namespace App\Entity;

use App\Repository\LoginChallengeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un défi de connexion (OTP) pour valider un nouvel appareil
 * Stocke uniquement le hash du code OTP, jamais le code en clair
 */
#[ORM\Entity(repositoryClass: LoginChallengeRepository::class)]
#[ORM\Table(name: 'login_challenge')]
#[ORM\Index(name: 'idx_user_device', columns: ['user_id', 'device_id'])]
#[ORM\Index(name: 'idx_expires_at', columns: ['expires_at'])]
class LoginChallenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    /**
     * UUID de l'appareil à valider
     */
    #[ORM\Column(length: 36)]
    private string $deviceId;

    /**
     * Hash du code OTP (jamais le code en clair)
     */
    #[ORM\Column(length: 255)]
    private string $otpHash;

    /**
     * Date de création du challenge
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Date d'expiration du challenge (10 minutes maximum)
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    /**
     * Nombre de tentatives de validation
     */
    #[ORM\Column(options: ['default' => 0])]
    private int $attempts = 0;

    /**
     * Maximum 5 tentatives autorisées
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Durée de validité du challenge (10 minutes)
     */
    private const VALIDITY_DURATION = 600; // 10 minutes en secondes

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = (new \DateTimeImmutable())->modify('+10 minutes');
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

    public function getOtpHash(): string
    {
        return $this->otpHash;
    }

    public function setOtpHash(string $otpHash): self
    {
        $this->otpHash = $otpHash;
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

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * Incrémente le nombre de tentatives
     */
    public function incrementAttempts(): self
    {
        $this->attempts++;
        return $this;
    }

    /**
     * Vérifie si le challenge est encore valide (non expiré et tentatives < max)
     */
    public function isValid(): bool
    {
        if ($this->expiresAt < new \DateTimeImmutable()) {
            return false; // Expiré
        }

        if ($this->attempts >= self::MAX_ATTEMPTS) {
            return false; // Trop de tentatives
        }

        return true;
    }

    /**
     * Vérifie si le challenge a atteint le maximum de tentatives
     */
    public function isMaxAttemptsReached(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Retourne le nombre de tentatives restantes
     */
    public function getRemainingAttempts(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts);
    }
}
