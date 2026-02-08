<?php

declare(strict_types=1);

namespace App\Dto;

final class NormalizedAlert
{
    public function __construct(
        public ?\DateTimeImmutable $occurredAt,
        public string $message,
        public string $severity,
        public string $category,
        public ?string $deviceExternalId,
        public ?string $serialNumber,
        public ?string $ipAddress,
        public ?string $hostname,
    ) {
    }

    public static function unrecognized(): self
    {
        return new self(
            occurredAt: null,
            message: 'Unrecognized payload',
            severity: 'info',
            category: 'unknown',
            deviceExternalId: null,
            serialNumber: null,
            ipAddress: null,
            hostname: null,
        );
    }

    /** @return array<string, mixed> */
    public function toMetaArray(): array
    {
        $meta = [
            'message' => $this->message,
            'severity' => $this->severity,
            'category' => $this->category,
        ];
        if ($this->occurredAt !== null) {
            $meta['occurredAt'] = $this->occurredAt->format(\DateTimeInterface::ATOM);
        }
        if ($this->deviceExternalId !== null) {
            $meta['deviceExternalId'] = $this->deviceExternalId;
        }
        if ($this->serialNumber !== null) {
            $meta['serialNumber'] = $this->serialNumber;
        }
        if ($this->ipAddress !== null) {
            $meta['ipAddress'] = $this->ipAddress;
        }
        if ($this->hostname !== null) {
            $meta['hostname'] = $this->hostname;
        }
        return $meta;
    }
}
