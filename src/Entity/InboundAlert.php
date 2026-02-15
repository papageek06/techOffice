<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InboundAlertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboundAlertRepository::class)]
#[ORM\Table(name: 'inbound_alert')]
#[ORM\Index(columns: ['status'], name: 'idx_inbound_alert_status')]
#[ORM\Index(columns: ['received_at'], name: 'idx_inbound_alert_received_at')]
#[ORM\UniqueConstraint(name: 'uniq_inbound_alert_message_id', columns: ['message_id'])]
class InboundAlert
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_PROCESSED = 'PROCESSED';
    public const STATUS_ERROR = 'ERROR';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true, name: 'message_id')]
    private ?string $messageId = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(length: 255, nullable: true, name: 'from_email')]
    private ?string $fromEmail = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, name: 'received_at')]
    private ?\DateTimeImmutable $receivedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $severity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\Column(length: 30)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column(type: Types::TEXT, nullable: true, name: 'error_message')]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, name: 'processed_at')]
    private ?\DateTimeImmutable $processedAt = null;

    /** @var Collection<int, InboundAlertAttachment> */
    #[ORM\OneToMany(targetEntity: InboundAlertAttachment::class, mappedBy: 'inboundAlert', cascade: ['persist', 'remove'])]
    private Collection $attachments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(?string $messageId): static
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): static
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeImmutable $receivedAt): static
    {
        $this->receivedAt = $receivedAt;
        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(?string $severity): static
    {
        $this->severity = $severity;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
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

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): static
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    /** @return Collection<int, InboundAlertAttachment> */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(InboundAlertAttachment $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setInboundAlert($this);
        }
        return $this;
    }
}
