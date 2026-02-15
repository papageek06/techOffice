<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\InboundAlert;
use App\Entity\InboundAlertAttachment;
use App\Message\ProcessInboundAlertMessage;
use App\Repository\InboundAlertAttachmentRepository;
use App\Repository\InboundAlertRepository;
use App\Service\CsvUploadService;
use App\Service\ImportCsvService;
use App\Service\InboundFileStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class InboundApiController extends AbstractController
{
    public function __construct(
        private readonly string $inboundToken,
        private readonly CsvUploadService $csvUploadService,
        private readonly ImportCsvService $importCsvService,
        private readonly InboundFileStorageService $fileStorage,
        private readonly EntityManagerInterface $em,
        private readonly InboundAlertRepository $alertRepository,
        private readonly InboundAlertAttachmentRepository $attachmentRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly string $projectDir,
    ) {
    }

    #[Route('/api/inbound/printaudit/report', name: 'api_inbound_printaudit_report', methods: ['POST'])]
    public function report(Request $request): JsonResponse
    {
        if (!$this->isTokenValid($request)) {
            $this->logger->warning('Inbound API report: invalid or missing token', [
                'ip' => $request->getClientIp(),
                'route' => 'api_inbound_printaudit_report',
            ]);
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $file = $request->files->get('csv_file');
        $tempPath = null;
        try {
            $tempPath = $this->csvUploadService->validateAndGetPath($file);
            $result = $this->importCsvService->import($tempPath);
            $imported = $result['success'];
            $errors = $result['errors'];
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Inbound API report: validation failed', [
                'ip' => $request->getClientIp(),
                'error' => $e->getMessage(),
            ]);
            return $this->json(['ok' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } finally {
            if ($tempPath !== null && is_file($tempPath)) {
                @unlink($tempPath);
            }
        }

        $this->logger->info('Inbound API report received', [
            'ip' => $request->getClientIp(),
            'route' => 'api_inbound_printaudit_report',
            'imported' => $imported,
            'errors_count' => count($errors),
        ]);

        return $this->json([
            'ok' => true,
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    #[Route('/api/inbound/mail/alert', name: 'api_inbound_mail_alert', methods: ['POST'])]
    public function alert(Request $request): JsonResponse
    {
        if (!$this->isTokenValid($request)) {
            $this->logger->warning('Inbound API alert: invalid or missing token', [
                'ip' => $request->getClientIp(),
                'route' => 'api_inbound_mail_alert',
            ]);
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $this->extractAlertPayload($request);
        if ($payload === null) {
            $this->logger->warning('Inbound API alert: invalid payload', [
                'ip' => $request->getClientIp(),
                'route' => 'api_inbound_mail_alert',
            ]);
            return $this->json(['ok' => false, 'error' => 'Invalid payload: expected JSON body or multipart with payload field'], Response::HTTP_BAD_REQUEST);
        }

        // Structure alignée sur fetch.js : messageId, subject, from, receivedAt, severity, body (buildBody), tags
        $alert = new InboundAlert();
        $alert->setMessageId($payload['messageId'] ?? null);
        $alert->setSubject($payload['subject'] ?? null);
        $alert->setFromEmail($payload['from'] ?? $payload['fromEmail'] ?? null);
        $body = isset($payload['body']) && $payload['body'] !== '' ? trim((string) $payload['body']) : null;
        $alert->setBody($body !== '' ? $body : null);
        $alert->setSeverity($payload['severity'] ?? 'info');
        if (isset($payload['tags']) && \is_array($payload['tags'])) {
            $alert->setTags($payload['tags']);
        }
        if (!empty($payload['receivedAt'])) {
            try {
                $alert->setReceivedAt(new \DateTimeImmutable($payload['receivedAt']));
            } catch (\Exception) {
                // ignore
            }
        }

        $attachmentsSaved = 0;
        // Ne pas utiliser all('attachments') : avec 1 seul fichier PHP met un UploadedFile, pas un array → BadRequestException
        $attachments = $request->files->get('attachments') ?? $request->files->get('attachments[]');
        $attachments = \is_array($attachments) ? $attachments : ($attachments ? [$attachments] : []);
        foreach ($attachments as $uploadedFile) {
            if (!$uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile || !$uploadedFile->isValid()) {
                continue;
            }
            try {
                $stored = $this->fileStorage->store($uploadedFile);
                if ($this->attachmentRepository->findBySha256($stored['sha256']) !== null) {
                    $fullPath = $this->projectDir . '/var/inbound/' . $stored['path'];
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                    $this->logger->info('Inbound API alert: attachment skipped (duplicate sha256)', [
                        'sha256' => $stored['sha256'],
                    ]);
                    continue;
                }
                $attachment = new InboundAlertAttachment();
                $attachment->setOriginalName($stored['originalName']);
                $attachment->setMimeType($stored['mimeType']);
                $attachment->setSize($stored['size']);
                $attachment->setSha256($stored['sha256']);
                $attachment->setStoredPath($stored['path']);
                $attachment->setStoredAt(new \DateTimeImmutable());
                $alert->addAttachment($attachment);
                $attachmentsSaved++;
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning('Inbound API alert: attachment rejected', [
                    'file' => $uploadedFile->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->em->persist($alert);
        $this->em->flush();

        $this->messageBus->dispatch(new ProcessInboundAlertMessage($alert->getId()));

        $this->logger->info('Inbound API alert received', [
            'ip' => $request->getClientIp(),
            'route' => 'api_inbound_mail_alert',
            'subject' => $alert->getSubject(),
            'attachmentsCount' => $attachmentsSaved,
        ]);

        return $this->json([
            'ok' => true,
            'alertId' => $alert->getId(),
            'attachmentsSaved' => $attachmentsSaved,
        ]);
    }

    private function isTokenValid(Request $request): bool
    {
        if ($this->inboundToken === '') {
            return false;
        }
        $header = $request->headers->get('X-Inbound-Token');
        return $header !== null && $header !== '' && hash_equals($this->inboundToken, $header);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractAlertPayload(Request $request): ?array
    {
        $contentType = $request->headers->get('Content-Type', '');
        if (stripos($contentType, 'application/json') !== false) {
            $body = $request->getContent();
            $decoded = json_decode($body, true);
            return \is_array($decoded) ? $decoded : null;
        }
        if (stripos($contentType, 'multipart/form-data') !== false) {
            $payloadRaw = $request->request->get('payload');
            if (\is_string($payloadRaw)) {
                $decoded = json_decode($payloadRaw, true);
                if (\is_array($decoded)) {
                    return $decoded;
                }
            }
            // Fallback : payload envoyé comme fichier (ex. form-data avec Buffer)
            $payloadFile = $request->files->get('payload');
            if ($payloadFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && $payloadFile->isValid()) {
                $payloadRaw = file_get_contents($payloadFile->getPathname());
                if ($payloadRaw !== false) {
                    $decoded = json_decode($payloadRaw, true);
                    if (\is_array($decoded)) {
                        return $decoded;
                    }
                }
            }
            $this->logger->warning('Inbound API alert: multipart sans payload valide', [
                'requestKeys' => array_keys($request->request->all()),
                'filesKeys' => array_keys($request->files->all()),
            ]);
        }
        return null;
    }
}
