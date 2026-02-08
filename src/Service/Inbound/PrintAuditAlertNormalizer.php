<?php

declare(strict_types=1);

namespace App\Service\Inbound;

use App\Dto\NormalizedAlert;

/**
 * Tente d'extraire une alerte normalisée depuis headers + payload (array ou raw).
 * Aucune hypothèse sur le format exact PrintAudit/FM : on cherche des clés courantes.
 */
final class PrintAuditAlertNormalizer
{
    private const SEVERITIES = ['info', 'warning', 'critical'];
    private const CATEGORIES = ['toner_low', 'offline', 'paper_jam', 'service', 'unknown'];

    /**
     * @param array<string, string|string[]>|null $headers
     * @param array<string, mixed>|null $payload
     */
    public function normalize(?array $headers, ?array $payload, string $rawFallback = ''): NormalizedAlert
    {
        $payload = $payload ?? [];
        $flat = $this->flatten($payload);
        $occurredAt = $this->extractOccurredAt($headers, $payload, $flat);
        $message = $this->extractString($flat, ['message', 'msg', 'description', 'text', 'body', 'alert_message', 'title']) ?? 'Unrecognized payload';
        $severity = $this->extractSeverity($flat);
        $category = $this->extractCategory($flat, $message);
        $deviceExternalId = $this->extractString($flat, ['device_id', 'deviceId', 'external_id', 'externalId', 'device_external_id', 'printer_id', 'printerId']);
        $serialNumber = $this->extractString($flat, ['serial', 'serial_number', 'serialNumber', 'serialNumber']);
        $ipAddress = $this->extractString($flat, ['ip', 'ip_address', 'ipAddress', 'host_ip']);
        $hostname = $this->extractString($flat, ['hostname', 'host_name', 'device_name', 'name', 'printer_name']);

        if ($message === 'Unrecognized payload' && $rawFallback !== '') {
            $message = 'Unrecognized payload (' . strlen($rawFallback) . ' bytes)';
        }

        return new NormalizedAlert(
            occurredAt: $occurredAt,
            message: $message,
            severity: $severity,
            category: $category,
            deviceExternalId: $deviceExternalId,
            serialNumber: $serialNumber,
            ipAddress: $ipAddress,
            hostname: $hostname,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function flatten(array $payload, string $prefix = ''): array
    {
        $out = [];
        foreach ($payload as $k => $v) {
            $key = $prefix !== '' ? $prefix . '.' . $k : (string) $k;
            if (\is_array($v) && !$this->isList($v)) {
                $out = array_merge($out, $this->flatten($v, $key));
            } else {
                $out[$key] = $v;
            }
        }
        return $out;
    }

    /** @param array<mixed> $a */
    private function isList(array $a): bool
    {
        if ($a === []) {
            return true;
        }
        return array_keys($a) === range(0, count($a) - 1);
    }

    /**
     * @param array<string, mixed> $flat
     * @param list<string> $candidates
     */
    private function extractString(array $flat, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            foreach ([$c, str_replace('_', '', $c)] as $key) {
                if (isset($flat[$key]) && \is_string($flat[$key]) && $flat[$key] !== '') {
                    return $flat[$key];
                }
            }
        }
        foreach ($flat as $k => $v) {
            if (\is_string($v) && (stripos($k, 'serial') !== false || stripos($k, 'ip') !== false || stripos($k, 'host') !== false || stripos($k, 'device') !== false)) {
                if ($v !== '') {
                    return $v;
                }
            }
        }
        return null;
    }

    /**
     * @param array<string, string|string[]>|null $headers
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $flat
     */
    private function extractOccurredAt(?array $headers, array $payload, array $flat): ?\DateTimeImmutable
    {
        $candidates = ['occurred_at', 'occurredAt', 'timestamp', 'date', 'time', 'created_at', 'createdAt', 'event_time'];
        foreach ($candidates as $c) {
            if (isset($flat[$c])) {
                $v = $flat[$c];
                if (\is_string($v)) {
                    try {
                        return new \DateTimeImmutable($v);
                    } catch (\Exception) {
                    }
                }
                if ($v instanceof \DateTimeInterface) {
                    return \DateTimeImmutable::createFromInterface($v);
                }
            }
        }
        if (isset($payload['timestamp']) && \is_numeric($payload['timestamp'])) {
            try {
                return (new \DateTimeImmutable())->setTimestamp((int) $payload['timestamp']);
            } catch (\Exception) {
            }
        }
        return null;
    }

    /** @param array<string, mixed> $flat */
    private function extractSeverity(array $flat): string
    {
        $v = $this->extractString($flat, ['severity', 'level', 'priority', 'type']);
        if ($v !== null) {
            $v = strtolower($v);
            foreach (self::SEVERITIES as $s) {
                if (str_contains($v, $s)) {
                    return $s;
                }
            }
        }
        return 'info';
    }

    /** @param array<string, mixed> $flat */
    private function extractCategory(array $flat, string $message): string
    {
        $v = $this->extractString($flat, ['category', 'event_type', 'eventType', 'alert_type', 'code']);
        if ($v !== null) {
            $v = strtolower($v);
            foreach (self::CATEGORIES as $c) {
                if (str_contains($v, $c) || $c === 'unknown') {
                    if ($c !== 'unknown') {
                        return $c;
                    }
                }
            }
        }
        $m = strtolower($message);
        if (str_contains($m, 'toner') || str_contains($m, 'cartridge')) {
            return 'toner_low';
        }
        if (str_contains($m, 'offline') || str_contains($m, 'unreachable')) {
            return 'offline';
        }
        if (str_contains($m, 'paper') || str_contains($m, 'jam')) {
            return 'paper_jam';
        }
        if (str_contains($m, 'service') || str_contains($m, 'maintenance')) {
            return 'service';
        }
        return 'unknown';
    }
}
