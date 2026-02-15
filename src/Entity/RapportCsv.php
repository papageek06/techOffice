<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RapportCsvRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rapport CSV reçu (mail avec PJ CSV) : chemin stocké pour consultation, import déjà envoyé au flux des rapports.
 */
#[ORM\Entity(repositoryClass: RapportCsvRepository::class)]
#[ORM\Table(name: 'rapport_csv')]
#[ORM\Index(columns: ['created_at'], name: 'idx_rapport_csv_created_at')]
class RapportCsv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Chemin relatif sous var/inbound/ (ex. 2026/02/fichier_xxx.csv) */
    #[ORM\Column(length: 500, name: 'stored_path')]
    private string $storedPath;

    #[ORM\ManyToOne(targetEntity: InboundAlert::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL', name: 'inbound_alert_id')]
    private ?InboundAlert $inboundAlert = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStoredPath(): string
    {
        return $this->storedPath;
    }

    public function setStoredPath(string $storedPath): static
    {
        $this->storedPath = $storedPath;
        return $this;
    }

    public function getInboundAlert(): ?InboundAlert
    {
        return $this->inboundAlert;
    }

    public function setInboundAlert(?InboundAlert $inboundAlert): static
    {
        $this->inboundAlert = $inboundAlert;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
