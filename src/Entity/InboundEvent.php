<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InboundEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InboundEventRepository::class)]
#[ORM\Table(name: 'inbound_event')]
#[ORM\Index(name: 'idx_inbound_event_provider', columns: ['provider'])]
#[ORM\Index(name: 'idx_inbound_event_status', columns: ['status'])]
#[ORM\Index(name: 'idx_inbound_event_received_at', columns: ['received_at'])]
#[ORM\UniqueConstraint(name: 'uniq_inbound_event_fingerprint', columns: ['fingerprint'])]
class InboundEvent
{
    public const STATUS_RECEIVED = 'received';
    public const STATUS_PARSED = 'parsed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';

    public const PROVIDER_PRINTAUDIT_FM = 'printaudit_fm';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $provider;

    #[ORM\Column(length: 120)]
    private string $endpoint;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contentType = null;

    /** @var array<string, string|string[]>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $headers = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $payloadRaw = '';

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payloadJson = null;

    #[ORM\Column(length: 30)]
    private string $status = self::STATUS_RECEIVED;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $parseError = null;

    #[ORM\Column(length: 64)]
    private string $fingerprint;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Imprimante $imprimante = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $meta = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeImmutable $receivedAt): static
    {
        $this->receivedAt = $receivedAt;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): static
    {
        $this->contentType = $contentType;
        return $this;
    }

    /** @return array<string, string|string[]>|null */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /** @param array<string, string|string[]>|null $headers */
    public function setHeaders(?array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function getPayloadRaw(): string
    {
        return $this->payloadRaw;
    }

    public function setPayloadRaw(string $payloadRaw): static
    {
        $this->payloadRaw = $payloadRaw;
        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getPayloadJson(): ?array
    {
        return $this->payloadJson;
    }

    /** @param array<string, mixed>|null $payloadJson */
    public function setPayloadJson(?array $payloadJson): static
    {
        $this->payloadJson = $payloadJson;
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

    public function getParseError(): ?string
    {
        return $this->parseError;
    }

    public function setParseError(?string $parseError): static
    {
        $this->parseError = $parseError;
        return $this;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(string $fingerprint): static
    {
        $this->fingerprint = $fingerprint;
        return $this;
    }

    public function getImprimante(): ?Imprimante
    {
        return $this->imprimante;
    }

    public function setImprimante(?Imprimante $imprimante): static
    {
        $this->imprimante = $imprimante;
        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /** @param array<string, mixed>|null $meta */
    public function setMeta(?array $meta): static
    {
        $this->meta = $meta;
        return $this;
    }
}
