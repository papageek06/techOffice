<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InboundAlertAttachmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboundAlertAttachmentRepository::class)]
#[ORM\Table(name: 'inbound_alert_attachment')]
#[ORM\Index(columns: ['inbound_alert_id'], name: 'idx_inbound_alert_attachment_alert')]
#[ORM\UniqueConstraint(name: 'uniq_inbound_alert_attachment_sha256', columns: ['sha256'])]
class InboundAlertAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InboundAlert::class, inversedBy: 'attachments')]
    #[ORM\JoinColumn(nullable: false, name: 'inbound_alert_id', onDelete: 'CASCADE')]
    private ?InboundAlert $inboundAlert = null;

    #[ORM\Column(length: 500, name: 'original_name')]
    private string $originalName;

    #[ORM\Column(length: 100, nullable: true, name: 'mime_type')]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $size = null;

    #[ORM\Column(length: 64)]
    private string $sha256;

    #[ORM\Column(length: 500, name: 'stored_path')]
    private string $storedPath;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'stored_at')]
    private \DateTimeImmutable $storedAt;

    public function __construct()
    {
        $this->storedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getSha256(): string
    {
        return $this->sha256;
    }

    public function setSha256(string $sha256): static
    {
        $this->sha256 = $sha256;
        return $this;
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

    public function getStoredAt(): \DateTimeImmutable
    {
        return $this->storedAt;
    }

    public function setStoredAt(\DateTimeImmutable $storedAt): static
    {
        $this->storedAt = $storedAt;
        return $this;
    }
}
