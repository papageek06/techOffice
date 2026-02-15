<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Valide et prépare un upload CSV pour import (partagé entre ImportController et API report).
 */
final class CsvUploadService
{
    public function validateAndGetPath(UploadedFile|null $file): string
    {
        if (!$file || !$file->isValid()) {
            throw new \InvalidArgumentException('Aucun fichier CSV valide fourni.');
        }
        if (strtolower($file->getClientOriginalExtension() ?? '') !== 'csv') {
            throw new \InvalidArgumentException('Le fichier doit être au format CSV.');
        }
        $tempPath = sys_get_temp_dir() . '/' . uniqid('csv_import_') . '.csv';
        $file->move(sys_get_temp_dir(), basename($tempPath));
        return $tempPath;
    }
}
