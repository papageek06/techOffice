<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\DefautImprimante;
use App\Entity\InboundAlert;
use App\Entity\InboundAlertAttachment;
use App\Entity\RapportCsv;
use App\Enum\TypeDefautAlert;
use App\Message\ProcessInboundAlertMessage;
use App\Repository\InboundAlertRepository;
use App\Repository\ImprimanteRepository;
use App\Service\DeductTonerForAlertService;
use App\Service\ImportCsvService;
use App\Service\Inbound\SmartAlertBodyParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessInboundAlertHandler
{
    public function __construct(
        private readonly InboundAlertRepository $alertRepository,
        private readonly ImprimanteRepository $imprimanteRepository,
        private readonly EntityManagerInterface $em,
        private readonly ImportCsvService $importCsvService,
        private readonly SmartAlertBodyParser $smartAlertParser,
        private readonly DeductTonerForAlertService $deductTonerService,
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
                // Rapport CSV : mail avec pièce(s) jointe(s) CSV → import + enregistrer la route pour consultation
                foreach ($csvAttachments as $attachment) {
                    $fullPath = $this->projectDir . '/var/inbound/' . $attachment->getStoredPath();
                    if (is_file($fullPath) && is_readable($fullPath)) {
                        $result = $this->importCsvService->import($fullPath);
                        $rapport = new RapportCsv();
                        $rapport->setStoredPath($attachment->getStoredPath());
                        $rapport->setInboundAlert($alert);
                        $this->em->persist($rapport);
                        $this->logger->info('Inbound rapport CSV importé et enregistré', [
                            'alertId' => $alert->getId(),
                            'storedPath' => $attachment->getStoredPath(),
                            'success' => $result['success'],
                            'errors' => $result['errors'],
                            'skipped' => $result['skipped'] ?? 0,
                        ]);
                    }
                }
            } elseif ($attachments->count() === 0) {
                // Smart Alert : pas de pièce jointe → traiter le body (site, imprimante, type défaut, déduction toner)
                $body = $alert->getBody() ?? '';
                if ($body !== '') {
                    $parsed = $this->smartAlertParser->parse($body, $alert->getReceivedAt());
                    foreach ($parsed as $item) {
                        $defaut = new DefautImprimante();
                        $defaut->setSiteNom($item['siteNom']);
                        $defaut->setTypeDefaut($item['typeDefaut']);
                        $defaut->setMessage($item['message']);
                        $defaut->setCouleurToner($item['couleurToner']);
                        $defaut->setMachineSerial($item['serial']);
                        $defaut->setMachineIp($item['ip']);
                        $defaut->setMachineNom($item['machineNom']);
                        $defaut->setInboundAlert($alert);
                        $defaut->setReceivedAt($alert->getReceivedAt());

                        $imprimante = null;
                        if (!empty($item['serial'])) {
                            $imprimante = $this->imprimanteRepository->findOneByNumeroSerie($item['serial']);
                        }
                        if ($imprimante === null && !empty($item['ip'])) {
                            $imprimante = $this->imprimanteRepository->findOneByAdresseIp($item['ip']);
                        }
                        if ($imprimante !== null) {
                            $defaut->setImprimante($imprimante);
                            if ($item['typeDefaut'] === TypeDefautAlert::CHANGEMENT_CARTOUCHE && $item['couleurToner'] !== null) {
                                $this->deductTonerService->deductOneTonerForImprimante($imprimante, $item['couleurToner']);
                            }
                        }

                        $this->em->persist($defaut);
                    }
                    if (\count($parsed) > 0) {
                        $this->logger->info('Inbound alerte parsée et défauts enregistrés', [
                            'alertId' => $alert->getId(),
                            'subject' => $alert->getSubject(),
                            'defautsCount' => count($parsed),
                        ]);
                    }
                }
            } else {
                // Pièce(s) jointe(s) mais pas CSV → rapport non géré, pas de parsing body
                $this->logger->info('Inbound alerte avec pièces jointes non-CSV (ignoré pour défauts)', [
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
