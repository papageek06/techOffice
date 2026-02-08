<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Dto\NormalizedAlert;
use App\Entity\InboundEvent;
use App\Message\ProcessInboundEventMessage;
use App\Repository\InboundEventRepository;
use App\Service\Inbound\PrintAuditAlertNormalizer;
use App\Service\Inbound\PrinterResolver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessInboundEventHandler
{
    public function __construct(
        private readonly InboundEventRepository $inboundEventRepository,
        private readonly EntityManagerInterface $em,
        private readonly PrintAuditAlertNormalizer $normalizer,
        private readonly PrinterResolver $printerResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProcessInboundEventMessage $message): void
    {
        $id = $message->getInboundEventId();
        $event = $this->inboundEventRepository->find($id);
        if ($event === null) {
            $this->logger->warning('Inbound event not found', ['id' => $id, 'channel' => 'inbound_alerts']);
            return;
        }

        try {
            $payload = $this->parsePayload($event);
            $headers = $event->getHeaders();
            $normalized = $this->normalizer->normalize($headers, $payload, $event->getPayloadRaw());
            $meta = $normalized->toMetaArray();
            $event->setMeta($meta);

            $this->printerResolver->resolveAndLink($event, $meta);

            $event->setStatus(InboundEvent::STATUS_PARSED);
            $event->setParseError(null);
        } catch (\Throwable $e) {
            $event->setStatus(InboundEvent::STATUS_FAILED);
            $event->setParseError($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine());
            $this->logger->error('Inbound event parse failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'channel' => 'inbound_alerts',
            ]);
        }

        $this->em->flush();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parsePayload(InboundEvent $event): ?array
    {
        $raw = $event->getPayloadRaw();
        $contentType = $event->getContentType() ?? '';

        if ($event->getPayloadJson() !== null) {
            return $event->getPayloadJson();
        }

        if (stripos($contentType, 'application/json') !== false) {
            $decoded = json_decode($raw, true);
            return \is_array($decoded) ? $decoded : null;
        }

        if (stripos($contentType, 'application/xml') !== false || stripos($contentType, 'text/xml') !== false) {
            return $this->xmlToArray($raw);
        }

        if (stripos($contentType, 'text/csv') !== false) {
            return $this->csvToArray($raw);
        }

        if (stripos($contentType, 'application/x-www-form-urlencoded') !== false || stripos($contentType, 'multipart/form-data') !== false) {
            parse_str($raw, $parsed);
            return \is_array($parsed) ? $parsed : null;
        }

        $decoded = json_decode($raw, true);
        if (\is_array($decoded)) {
            return $decoded;
        }
        return $this->xmlToArray($raw);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function xmlToArray(string $raw): ?array
    {
        $prev = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($raw);
            if ($xml === false) {
                return null;
            }
            $json = json_encode($xml);
            if ($json === false) {
                return null;
            }
            $decoded = json_decode($json, true);
            return \is_array($decoded) ? $decoded : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function csvToArray(string $raw): array
    {
        $lines = array_filter(explode("\n", str_replace("\r", '', $raw)));
        if ($lines === []) {
            return [];
        }
        $headers = str_getcsv(array_shift($lines));
        $out = ['rows' => []];
        foreach ($lines as $line) {
            $row = str_getcsv($line);
            $out['rows'][] = array_combine($headers, array_pad($row, count($headers), null)) ?: [];
        }
        return $out;
    }
}
