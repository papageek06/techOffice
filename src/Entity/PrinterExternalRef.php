<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PrinterExternalRefRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrinterExternalRefRepository::class)]
#[ORM\Table(name: 'printer_external_ref')]
#[ORM\UniqueConstraint(name: 'uniq_printer_external_ref_provider_external', columns: ['provider', 'external_id'])]
#[ORM\Index(name: 'idx_printer_external_ref_imprimante', columns: ['imprimante_id'])]
class PrinterExternalRef
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Imprimante $imprimante;

    #[ORM\Column(length: 80)]
    private string $provider;

    #[ORM\Column(length: 255)]
    private string $externalId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $lastSeenAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImprimante(): Imprimante
    {
        return $this->imprimante;
    }

    public function setImprimante(Imprimante $imprimante): static
    {
        $this->imprimante = $imprimante;
        return $this;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getLastSeenAt(): \DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(\DateTimeImmutable $lastSeenAt): static
    {
        $this->lastSeenAt = $lastSeenAt;
        return $this;
    }
}
