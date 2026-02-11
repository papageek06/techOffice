<?php

namespace App\Entity;

use App\Repository\InboundAttachmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboundAttachmentRepository::class)]
#[ORM\Table(name: 'inbound_attachment')]
#[ORM\Index(columns: ['inbound_email_id'], name: 'idx_inbound_email_id')]
class InboundAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InboundEmail::class, inversedBy: 'attachments')]
    #[ORM\JoinColumn(nullable: false, name: 'inbound_email_id')]
    private ?InboundEmail $inboundEmail = null;

    #[ORM\Column(length: 500, name: 'filename_original')]
    private ?string $filenameOriginal = null;

    #[ORM\Column(length: 100, nullable: true, name: 'mime_type')]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $size = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $sha256 = null;

    #[ORM\Column(length: 500, name: 'stored_path')]
    private ?string $storedPath = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'stored_at')]
    private ?\DateTimeInterface $storedAt = null;

    public function __construct()
    {
        $this->storedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInboundEmail(): ?InboundEmail
    {
        return $this->inboundEmail;
    }

    public function setInboundEmail(?InboundEmail $inboundEmail): static
    {
        $this->inboundEmail = $inboundEmail;

        return $this;
    }

    public function getFilenameOriginal(): ?string
    {
        return $this->filenameOriginal;
    }

    public function setFilenameOriginal(string $filenameOriginal): static
    {
        $this->filenameOriginal = $filenameOriginal;

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

    public function getSha256(): ?string
    {
        return $this->sha256;
    }

    public function setSha256(string $sha256): static
    {
        $this->sha256 = $sha256;

        return $this;
    }

    public function getStoredPath(): ?string
    {
        return $this->storedPath;
    }

    public function setStoredPath(string $storedPath): static
    {
        $this->storedPath = $storedPath;

        return $this;
    }

    public function getStoredAt(): ?\DateTimeInterface
    {
        return $this->storedAt;
    }

    public function setStoredAt(\DateTimeInterface $storedAt): static
    {
        $this->storedAt = $storedAt;

        return $this;
    }
}
