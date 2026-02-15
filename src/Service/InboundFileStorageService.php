<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class InboundFileStorageService
{
    private const ALLOWED_EXTENSIONS = ['csv', 'pdf', 'txt', 'png', 'jpg', 'jpeg'];

    /** Extensions interdites (sécurité) */
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'ps1',
        'js', 'vbs', 'wsf', 'jar', 'war',
        'htaccess', 'htpasswd',
    ];

    public function __construct(
        private string $projectDir,
        private string $maxFileSize,
        private ?LoggerInterface $logger = null,
    ) {
        $this->maxFileSizeBytes = self::parseSizeToBytes($maxFileSize);
    }

    private int $maxFileSizeBytes;

    /**
     * Stocke un fichier uploadé dans var/inbound/YYYY/MM/ avec nom sécurisé et calcule sha256.
     *
     * @return array{path: string, sha256: string, originalName: string, mimeType: string|null, size: int}
     * @throws \InvalidArgumentException si extension non autorisée ou taille dépassée
     */
    public function store(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: '');

        // Fichier sans nom ou extension non autorisée (ex. mail-fetcher envoie attachment.bin) → traiter comme .txt
        if ($originalName === '' || !\in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $originalName = $originalName !== '' ? $originalName : 'attachment';
            $ext = 'txt';
            $this->logger?->info('Inbound file: nom normalisé pour stockage', [
                'originalName' => $file->getClientOriginalName(),
                'storedAs' => $originalName . '.' . $ext,
            ]);
        }

        if (\in_array($ext, self::FORBIDDEN_EXTENSIONS, true)) {
            $this->logger?->warning('Inbound file rejected: forbidden extension', [
                'extension' => $ext,
                'originalName' => $originalName,
            ]);
            throw new \InvalidArgumentException("Extension non autorisée : {$ext}");
        }

        if (!\in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $this->logger?->warning('Inbound file rejected: extension not in allowed list', [
                'extension' => $ext,
                'originalName' => $originalName,
            ]);
            throw new \InvalidArgumentException("Extension non autorisée. Autorisées : " . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        if ($file->getSize() > $this->maxFileSizeBytes) {
            throw new \InvalidArgumentException('Fichier trop volumineux (max ' . round($this->maxFileSizeBytes / 1024 / 1024, 1) . ' Mo).');
        }

        $sha256 = hash_file('sha256', $file->getPathname());
        if ($sha256 === false) {
            throw new \RuntimeException('Impossible de calculer le hash du fichier.');
        }

        $now = new \DateTimeImmutable();
        $dir = $this->projectDir . '/var/inbound/' . $now->format('Y') . '/' . $now->format('m');
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : {$dir}");
            }
        }

        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $safeBase = substr($safeBase ?: 'file', 0, 100);
        $filename = $safeBase . '_' . uniqid('', true) . '.' . $ext;
        $targetPath = $dir . '/' . $filename;

        $file->move($dir, $filename);
        $storedRelative = $now->format('Y') . '/' . $now->format('m') . '/' . $filename;

        return [
            'path' => $storedRelative,
            'sha256' => $sha256,
            'originalName' => $originalName,
            'mimeType' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
        ];
    }

    public static function parseSizeToBytes(string $size): int
    {
        $size = trim($size);
        $unit = strtoupper(substr($size, -1));
        $value = (int) $size;
        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => (int) $size,
        };
    }
}
