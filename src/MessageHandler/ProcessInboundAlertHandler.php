<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\InboundAlert;
use App\Entity\InboundAlertAttachment;
use App\Message\ProcessInboundAlertMessage;
use App\Repository\InboundAlertRepository;
use App\Service\ImportCsvService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessInboundAlertHandler
{
    public function __construct(
        private readonly InboundAlertRepository $alertRepository,
        private readonly EntityManagerInterface $em,
        private readonly ImportCsvService $importCsvService,
        private readonly string $projectDir,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProcessInboundAlertMessage $message): void
    {
        $alert = $this->alertRepository->find($message->getAlertId());
        if ($alert === null) {
            $this->logger->warning('Inbound alert not found', ['alertId' => $message->getAlertId()]);
            return;
        }

        if ($alert->getStatus() !== InboundAlert::STATUS_NEW) {
            return;
        }

        try {
            $attachments = $alert->getAttachments();
            $csvAttachments = $attachments->filter(fn (InboundAlertAttachment $a) => $this->isCsvAttachment($a));

            if ($csvAttachments->count() > 0) {
                // Email avec pièce(s) jointe(s) CSV → rapport CSV (import)
                foreach ($csvAttachments as $attachment) {
                    $fullPath = $this->projectDir . '/var/inbound/' . $attachment->getStoredPath();
                    if (is_file($fullPath) && is_readable($fullPath)) {
                        $result = $this->importCsvService->import($fullPath);
                        $this->logger->info('Inbound rapport CSV importé', [
                            'alertId' => $alert->getId(),
                            'attachmentId' => $attachment->getId(),
                            'subject' => $alert->getSubject(),
                            'success' => $result['success'],
                            'errors' => $result['errors'],
                            'skipped' => $result['skipped'] ?? 0,
                        ]);
                    }
                }
            } elseif ($attachments->count() === 0) {
                // Email sans pièce jointe → alerte (Smart Alert Katun/PrintAudit, etc.) : traitée comme alerte
                $this->logger->info('Inbound alerte traitée (sans pièce jointe)', [
                    'alertId' => $alert->getId(),
                    'subject' => $alert->getSubject(),
                    'from' => $alert->getFromEmail(),
                    'receivedAt' => $alert->getReceivedAt()?->format(\DateTimeInterface::ATOM),
                ]);
            } else {
                // Pièces jointes mais pas de CSV → enregistré, pas d'import rapport
                $this->logger->info('Inbound alerte avec pièces jointes non-CSV', [
                    'alertId' => $alert->getId(),
                    'subject' => $alert->getSubject(),
                    'attachmentsCount' => $attachments->count(),
                ]);
            }

            $alert->setStatus(InboundAlert::STATUS_PROCESSED);
            $alert->setProcessedAt(new \DateTimeImmutable());
            $alert->setErrorMessage(null);
        } catch (\Throwable $e) {
            $alert->setStatus(InboundAlert::STATUS_ERROR);
            $alert->setErrorMessage($e->getMessage());
            $this->logger->error('Inbound alert processing failed', [
                'alertId' => $alert->getId(),
                'error' => $e->getMessage(),
            ]);
        }

        $this->em->flush();
    }

    private function isCsvAttachment(InboundAlertAttachment $attachment): bool
    {
        $name = strtolower($attachment->getOriginalName());
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        return $ext === 'csv';
    }
}
