<?php

namespace App\Entity;

use App\Enum\InboundEmailStatus;
use App\Repository\InboundEmailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboundEmailRepository::class)]
#[ORM\Table(name: 'inbound_email')]
#[ORM\Index(columns: ['message_id'], name: 'idx_message_id')]
#[ORM\Index(columns: ['status'], name: 'idx_status')]
class InboundEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $messageId = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(length: 255, nullable: true, name: 'from_email')]
    private ?string $fromEmail = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, name: 'received_at')]
    private ?\DateTimeInterface $receivedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, name: 'processed_at')]
    private ?\DateTimeInterface $processedAt = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, name: 'error_message')]
    private ?string $errorMessage = null;

    #[ORM\OneToMany(targetEntity: InboundAttachment::class, mappedBy: 'inboundEmail', cascade: ['persist', 'remove'])]
    private Collection $attachments;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->status = InboundEmailStatus::NEW->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): static
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

    public function getReceivedAt(): ?\DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeInterface $receivedAt): static
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeInterface
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeInterface $processedAt): static
    {
        $this->processedAt = $processedAt;

        return $this;
    }

    public function getStatus(): ?string
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

    /**
     * @return Collection<int, InboundAttachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(InboundAttachment $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setInboundEmail($this);
        }

        return $this;
    }

    public function removeAttachment(InboundAttachment $attachment): static
    {
        if ($this->attachments->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getInboundEmail() === $this) {
                $attachment->setInboundEmail(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
