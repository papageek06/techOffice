<?php

namespace App\Service;

use App\Dto\EmailAttachmentDto;
use App\Entity\InboundAttachment;
use App\Entity\InboundEmail;
use App\Enum\InboundEmailStatus;
use App\Repository\InboundAttachmentRepository;
use App\Repository\InboundEmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class InboundMailImporter
{
    public function __construct(
        private ImapMailboxClient $imapClient,
        private EntityManagerInterface $entityManager,
        private InboundEmailRepository $emailRepository,
        private InboundAttachmentRepository $attachmentRepository,
        private string $attachmentsDir,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Importe les emails non lus
     * 
     * @return array{scanned: int, imported: int, skipped: int, errors: int, attachments: int}
     */
    public function importUnseenEmails(int $limit = 50, bool $dryRun = false): array
    {
        $stats = [
            'scanned' => 0,
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'attachments' => 0,
        ];

        try {
            $this->imapClient->connect();
            $emailNumbers = $this->imapClient->fetchUnseenEmails($limit);
            $stats['scanned'] = count($emailNumbers);

            foreach ($emailNumbers as $emailUid) {
                try {
                    $result = $this->importEmail($emailUid, $dryRun);
                    $stats['imported'] += $result['imported'] ? 1 : 0;
                    $stats['skipped'] += $result['skipped'] ? 1 : 0;
                    $stats['attachments'] += $result['attachments'];
                    
                    if ($result['error']) {
                        $stats['errors']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->logger?->error('Erreur lors de l\'import d\'un email', [
                        'emailUid' => $emailUid,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            $this->imapClient->close();
        }

        return $stats;
    }

    /**
     * Importe un email spécifique
     * 
     * @param int $emailUid UID de l'email
     * @return array{imported: bool, skipped: bool, attachments: int, error: bool}
     */
    private function importEmail(int $emailUid, bool $dryRun = false): array
    {
        $result = [
            'imported' => false,
            'skipped' => false,
            'attachments' => 0,
            'error' => false,
        ];

        try {
            // Récupérer les métadonnées
            $metadata = $this->imapClient->fetchEmailMetadata($emailUid);
            
            if (empty($metadata['messageId'])) {
                throw new \RuntimeException('Message-ID manquant pour l\'email UID #' . $emailUid);
            }

            $messageId = $metadata['messageId'];

            // Vérifier si l'email existe déjà
            $existingEmail = $this->emailRepository->findByMessageId($messageId);
            if ($existingEmail !== null) {
                $this->logger?->info('Email déjà importé, ignoré', ['messageId' => $messageId]);
                $result['skipped'] = true;
                return $result;
            }

            if ($dryRun) {
                $this->logger?->info('Mode dry-run: email serait importé', [
                    'messageId' => $messageId,
                    'subject' => $metadata['subject'],
                ]);
                $result['imported'] = true;
                return $result;
            }

            // Créer l'entité InboundEmail
            $inboundEmail = new InboundEmail();
            $inboundEmail->setMessageId($messageId);
            $inboundEmail->setSubject($metadata['subject']);
            $inboundEmail->setFromEmail($metadata['from']);
            $inboundEmail->setReceivedAt($metadata['date']);
            $inboundEmail->setStatus(InboundEmailStatus::NEW->value);

            $this->entityManager->persist($inboundEmail);

            // Récupérer et traiter les pièces jointes
            $attachments = $this->imapClient->fetchAttachments($emailUid);
            $attachmentCount = 0;

            foreach ($attachments as $attachmentDto) {
                try {
                    $saved = $this->saveAttachment($inboundEmail, $attachmentDto);
                    if ($saved) {
                        $attachmentCount++;
                    }
                } catch (\Exception $e) {
                    $this->logger?->error('Erreur lors de la sauvegarde d\'une pièce jointe', [
                        'email' => $messageId,
                        'filename' => $attachmentDto->originalName,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue avec les autres pièces jointes
                }
            }

            // Marquer comme traité
            $inboundEmail->setStatus(InboundEmailStatus::PROCESSED->value);
            $inboundEmail->setProcessedAt(new \DateTime());

            $this->entityManager->flush();

            // Marquer l'email comme lu dans IMAP
            $this->imapClient->markSeen($emailUid);

            $result['imported'] = true;
            $result['attachments'] = $attachmentCount;

            $this->logger?->info('Email importé avec succès', [
                'messageId' => $messageId,
                'attachments' => $attachmentCount,
            ]);

        } catch (\Exception $e) {
            $result['error'] = true;
            
            // Si l'email a été créé mais qu'une erreur s'est produite
            if (isset($inboundEmail) && $inboundEmail->getId() === null) {
                // L'email n'a pas encore été flush, on peut l'annuler
            } elseif (isset($inboundEmail)) {
                // L'email existe déjà, on marque l'erreur
                $inboundEmail->setStatus(InboundEmailStatus::ERROR->value);
                $inboundEmail->setErrorMessage($e->getMessage());
                $this->entityManager->flush();
            }

            $this->logger?->error('Erreur lors de l\'import de l\'email', [
                'emailUid' => $emailUid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $result;
    }

    /**
     * Sauvegarde une pièce jointe
     * 
     * @return bool true si sauvegardée, false si doublon
     */
    private function saveAttachment(InboundEmail $inboundEmail, EmailAttachmentDto $attachmentDto): bool
    {
        // Calculer le hash SHA256
        $sha256 = hash('sha256', $attachmentDto->content);

        // Vérifier si la pièce jointe existe déjà
        $existingAttachment = $this->attachmentRepository->findBySha256($sha256);
        if ($existingAttachment !== null) {
            $this->logger?->info('Pièce jointe déjà existante (doublon), ignorée', [
                'sha256' => $sha256,
                'filename' => $attachmentDto->originalName,
            ]);
            return false;
        }

        // Créer le répertoire de stockage (YYYY/MM)
        $now = new \DateTime();
        $year = $now->format('Y');
        $month = $now->format('m');
        $storageDir = $this->attachmentsDir . '/' . $year . '/' . $month;

        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Générer un nom de fichier sécurisé
        $extension = pathinfo($attachmentDto->originalName, PATHINFO_EXTENSION);
        $safeBasename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($attachmentDto->originalName, PATHINFO_FILENAME));
        $safeBasename = substr($safeBasename, 0, 100); // Limiter la longueur
        
        // Utiliser le hash pour éviter les collisions
        $filename = $safeBasename . '_' . substr($sha256, 0, 8);
        if ($extension) {
            $filename .= '.' . $extension;
        }

        $filePath = $storageDir . '/' . $filename;

        // Sauvegarder le fichier
        if (file_put_contents($filePath, $attachmentDto->content) === false) {
            throw new \RuntimeException(sprintf('Impossible d\'écrire le fichier: %s', $filePath));
        }

        // Créer l'entité InboundAttachment
        $attachment = new InboundAttachment();
        $attachment->setInboundEmail($inboundEmail);
        $attachment->setFilenameOriginal($attachmentDto->originalName);
        $attachment->setMimeType($attachmentDto->mimeType);
        $attachment->setSize($attachmentDto->size);
        $attachment->setSha256($sha256);
        $attachment->setStoredPath($filePath);
        $attachment->setStoredAt(new \DateTime());

        $inboundEmail->addAttachment($attachment);
        $this->entityManager->persist($attachment);

        $this->logger?->info('Pièce jointe sauvegardée', [
            'filename' => $attachmentDto->originalName,
            'path' => $filePath,
            'size' => $attachmentDto->size,
        ]);

        return true;
    }
}
