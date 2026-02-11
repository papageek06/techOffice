<?php

namespace App\Service;

use App\Dto\EmailAttachmentDto;
use Psr\Log\LoggerInterface;

class ImapMailboxClient
{
    /** @var resource|\IMAP\Connection|null */
    private $connection = null;
    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $mailbox;
    private bool $ssl;

    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password,
        string $mailbox = 'INBOX',
        bool $ssl = true,
        private ?LoggerInterface $logger = null
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->mailbox = $mailbox;
        $this->ssl = $ssl;
    }

    /**
     * Connexion à la boîte IMAP
     * 
     * @throws \RuntimeException
     */
    public function connect(): void
    {
        if ($this->connection !== null) {
            return;
        }

        $server = sprintf(
            '{%s:%d/imap%s}%s',
            $this->host,
            $this->port,
            $this->ssl ? '/ssl' : '',
            $this->mailbox
        );

        $this->logger?->info('Connexion IMAP', [
            'server' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'mailbox' => $this->mailbox,
        ]);

        $connection = @imap_open($server, $this->user, $this->password, OP_HALFOPEN);

        if ($connection === false) {
            $error = imap_last_error();
            $this->logger?->error('Échec de connexion IMAP', ['error' => $error]);
            throw new \RuntimeException(sprintf('Impossible de se connecter à la boîte IMAP: %s', $error ?: 'Erreur inconnue'));
        }

        $this->connection = $connection;
        $this->logger?->info('Connexion IMAP réussie');
    }

    /**
     * Récupère les emails non lus
     * 
     * @return int[] UIDs des messages
     */
    public function fetchUnseenEmails(int $limit = 50): array
    {
        $this->ensureConnected();

        $emails = @imap_search($this->connection, 'UNSEEN', SE_UID);
        
        if ($emails === false) {
            $this->logger?->info('Aucun email non lu trouvé');
            return [];
        }

        // Limiter le nombre d'emails
        $emails = array_slice($emails, 0, $limit);
        
        $this->logger?->info(sprintf('%d email(s) non lu(s) trouvé(s)', count($emails)));

        return $emails;
    }

    /**
     * Récupère les métadonnées d'un email
     * 
     * @param int $emailUid UID de l'email (pas le numéro de séquence)
     * @return array{subject: string|null, from: string|null, date: \DateTimeInterface|null, messageId: string|null}
     */
    public function fetchEmailMetadata(int $emailUid): array
    {
        $this->ensureConnected();

        $header = @imap_headerinfo($this->connection, $emailUid, FT_UID);
        
        if ($header === false) {
            throw new \RuntimeException(sprintf('Impossible de récupérer les en-têtes de l\'email UID #%d', $emailUid));
        }

        $subject = $header->subject ?? null;
        $from = null;
        if (isset($header->from[0])) {
            $from = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        }
        $date = $header->date ? new \DateTime($header->date) : null;
        
        // Récupérer le Message-ID depuis les en-têtes bruts
        $rawHeader = @imap_fetchheader($this->connection, $emailUid, FT_UID);
        $messageId = null;
        if ($rawHeader && preg_match('/Message-ID:\s*(.+)/i', $rawHeader, $matches)) {
            $messageId = trim($matches[1], '<>');
        }

        return [
            'subject' => $subject,
            'from' => $from,
            'date' => $date,
            'messageId' => $messageId,
        ];
    }

    /**
     * Récupère les pièces jointes d'un email
     * 
     * @param int $emailUid UID de l'email (pas le numéro de séquence)
     * @return EmailAttachmentDto[]
     */
    public function fetchAttachments(int $emailUid): array
    {
        $this->ensureConnected();

        $structure = @imap_fetchstructure($this->connection, $emailUid, FT_UID);
        
        if ($structure === false) {
            throw new \RuntimeException(sprintf('Impossible de récupérer la structure de l\'email UID #%d', $emailUid));
        }

        $attachments = [];

        if (!isset($structure->parts) || !is_array($structure->parts)) {
            return $attachments;
        }

        $this->extractAttachments($structure->parts, $emailUid, $attachments);

        return $attachments;
    }

    /**
     * Extrait récursivement les pièces jointes
     */
    private function extractAttachments(array $parts, int $emailUid, array &$attachments, string $partNumber = ''): void
    {
        foreach ($parts as $index => $part) {
            $currentPartNumber = $partNumber ? $partNumber . '.' . ($index + 1) : (string)($index + 1);

            // Si c'est une pièce jointe
            if (isset($part->disposition) && strtolower($part->disposition) === 'attachment') {
                $filename = $this->getAttachmentFilename($part);
                if ($filename) {
                    $content = @imap_fetchbody($this->connection, $emailUid, $currentPartNumber, FT_UID);
                    
                    if ($content === false) {
                        continue;
                    }
                    
                    // Décoder selon l'encodage
                    $content = $this->decodeContent($content, $part->encoding ?? 0);
                    
                    $mimeType = $this->getMimeType($part);
                    $size = strlen($content);

                    $attachments[] = new EmailAttachmentDto(
                        $filename,
                        $mimeType,
                        $content,
                        $size
                    );
                }
            }

            // Récursion pour les sous-parties
            if (isset($part->parts) && is_array($part->parts)) {
                $this->extractAttachments($part->parts, $emailUid, $attachments, $currentPartNumber);
            }
        }
    }

    /**
     * Récupère le nom de fichier d'une pièce jointe
     */
    private function getAttachmentFilename($part): ?string
    {
        if (isset($part->dparameters)) {
            foreach ($part->dparameters as $param) {
                if (strtolower($param->attribute) === 'filename') {
                    return $param->value;
                }
            }
        }

        if (isset($part->parameters)) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    return $param->value;
                }
            }
        }

        return null;
    }

    /**
     * Récupère le type MIME
     */
    private function getMimeType($part): ?string
    {
        $mimeType = null;
        
        if (isset($part->subtype)) {
            $mimeType = $part->type . '/' . $part->subtype;
        } elseif (isset($part->type)) {
            $mimeType = $this->getMimeTypeFromType($part->type);
        }

        return $mimeType;
    }

    /**
     * Convertit le type numérique en type MIME
     */
    private function getMimeTypeFromType(int $type): string
    {
        return match ($type) {
            0 => 'text/plain',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'other',
            default => 'application/octet-stream',
        };
    }

    /**
     * Décode le contenu selon l'encodage
     */
    private function decodeContent(string $content, int $encoding): string
    {
        return match ($encoding) {
            0, 1 => $content, // 7bit, 8bit
            2 => imap_binary($content), // Binary
            3 => base64_decode($content), // Base64
            4 => quoted_printable_decode($content), // Quoted-printable
            5 => $content, // Other
            default => $content,
        };
    }

    /**
     * Marque un email comme lu
     * 
     * @param int $emailUid UID de l'email (pas le numéro de séquence)
     */
    public function markSeen(int $emailUid): void
    {
        $this->ensureConnected();

        $result = @imap_setflag_full($this->connection, (string)$emailUid, '\\Seen', ST_UID);
        
        if (!$result) {
            $error = imap_last_error();
            $this->logger?->warning('Impossible de marquer l\'email comme lu', [
                'emailUid' => $emailUid,
                'error' => $error,
            ]);
        } else {
            $this->logger?->info('Email marqué comme lu', ['emailUid' => $emailUid]);
        }
    }

    /**
     * Ferme la connexion IMAP
     */
    public function close(): void
    {
        if ($this->connection !== null) {
            imap_close($this->connection);
            $this->connection = null;
            $this->logger?->info('Connexion IMAP fermée');
        }
    }

    /**
     * Vérifie que la connexion est établie
     */
    private function ensureConnected(): void
    {
        if ($this->connection === null) {
            throw new \RuntimeException('La connexion IMAP n\'est pas établie. Appelez connect() d\'abord.');
        }
    }

    /**
     * Destructeur : ferme la connexion si elle est encore ouverte
     */
    public function __destruct()
    {
        $this->close();
    }
}
