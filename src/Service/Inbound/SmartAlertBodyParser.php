<?php

declare(strict_types=1);

namespace App\Service\Inbound;

use App\Enum\TypeDefautAlert;

/**
 * Parse le body texte des Smart Alerts (Katun/PrintAudit) pour en extraire site, machine, type de défaut.
 *
 * Basé sur les 20 dernières entrées inbound_alert : format "SMART ALERT POUR XXX / SITE PRINCIPAL",
 * "Nouvelle alerte : (date): TYPE - detail", "Changement de cartouche détecté... changé de 0% à 100%".
 */
final class SmartAlertBodyParser
{
    /**
     * @return list<array{siteNom: string, typeDefaut: TypeDefautAlert, message: string, couleurToner: ?string, serial: ?string, ip: ?string, machineNom: ?string}>
     */
    public function parse(string $body, ?\DateTimeImmutable $receivedAt): array
    {
        $body = trim($body);
        if ($body === '') {
            return [];
        }

        $siteNom = $this->extractSiteNom($body);
        $serial = $this->extractFirstSerial($body);
        $ip = $this->extractFirstIp($body);
        $machineNom = $this->extractFirstMachineName($body);

        $result = [];

        // Lignes "Nouvelle alerte : (date): ..."
        if (preg_match_all('/Nouvelle alerte\s*:\s*\([^)]+\)\s*:\s*(.+?)(?=\n\n|\nNouvelle alerte|$)/uis', $body, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $message = trim($m[1]);
                $typeDefaut = $this->classifyType($message);
                $couleurToner = $this->extractCouleurToner($message);
                $result[] = [
                    'siteNom' => $siteNom,
                    'typeDefaut' => $typeDefaut,
                    'message' => $message,
                    'couleurToner' => $couleurToner,
                    'serial' => $serial,
                    'ip' => $ip,
                    'machineNom' => $machineNom,
                ];
            }
        }

        // "Changement de cartouche détecté: ... changé de 0% à 100%"
        if (preg_match_all('/Changement de cartouche[^.]*?détecté[^.]*?\./ui', $body, $changeMatches)) {
            foreach ($changeMatches[0] as $msg) {
                $message = trim($msg);
                $couleurToner = $this->extractCouleurToner($message);
                $result[] = [
                    'siteNom' => $siteNom,
                    'typeDefaut' => TypeDefautAlert::CHANGEMENT_CARTOUCHE,
                    'message' => $message,
                    'couleurToner' => $couleurToner,
                    'serial' => $serial,
                    'ip' => $ip,
                    'machineNom' => $machineNom,
                ];
            }
        }

        // Si aucun bloc reconnu mais body contient "Toner" ou "cartouche", une seule entrée
        if ($result === [] && (stripos($body, 'toner') !== false || stripos($body, 'cartouche') !== false)) {
            $typeDefaut = $this->classifyType($body);
            $result[] = [
                'siteNom' => $siteNom,
                'typeDefaut' => $typeDefaut,
                'message' => strlen($body) > 2000 ? substr($body, 0, 1997) . '...' : $body,
                'couleurToner' => $this->extractCouleurToner($body),
                'serial' => $serial,
                'ip' => $ip,
                'machineNom' => $machineNom,
            ];
        }

        return $result;
    }

    private function extractSiteNom(string $body): string
    {
        if (preg_match('/SMART ALERT POUR\s+([^\/\n]+?)\s*\/\s*(?:SITE|site)/ui', $body, $m)) {
            return trim($m[1]);
        }
        return 'Inconnu';
    }

    private function extractFirstSerial(string $body): ?string
    {
        // Série type: 3132M190082, C748JA00141, 9153R640296 (alphanumeric 8-15)
        if (preg_match('/\b([A-Z0-9]{8,15})\b/i', $body, $m)) {
            $candidate = $m[1];
            if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    private function extractFirstIp(string $body): ?string
    {
        if (preg_match('/\b(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\b/', $body, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractFirstMachineName(string $body): ?string
    {
        // "RICOH IM C5500", "LEXMARK M3250" etc. après "Nom de la machine" ou en début de ligne
        if (preg_match('/(?:Fabricant|Nom de la machine)\s*\n?\s*([A-Za-z0-9\s\-]+?)(?:\s{2,}|\n|Client)/u', $body, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/\b(RICOH|LEXMARK|HP|CANON|KYOCERA)\s+[\w\s]+\b/ui', $body, $m)) {
            return trim($m[0]);
        }
        return null;
    }

    private function classifyType(string $message): TypeDefautAlert
    {
        $m = mb_strtolower($message);
        if (str_contains($m, 'changement de cartouche') || preg_match('/changé de 0% à 100%/', $m)) {
            return TypeDefautAlert::CHANGEMENT_CARTOUCHE;
        }
        if (str_contains($m, 'caspillage') || str_contains($m, 'spillage')) {
            return TypeDefautAlert::SPILLAGE;
        }
        if (str_contains($m, 'toner bas') || str_contains($m, '% restants') || str_contains($m, ', bas')) {
            return TypeDefautAlert::NIVEAU_ENCRE;
        }
        return TypeDefautAlert::AUTRE;
    }

    private function extractCouleurToner(string $message): ?string
    {
        $m = mb_strtolower($message);
        if (str_contains($m, 'noir') || str_contains($m, 'black')) {
            return 'noir';
        }
        if (str_contains($m, 'cyan')) {
            return 'cyan';
        }
        if (str_contains($m, 'magenta')) {
            return 'magenta';
        }
        if (str_contains($m, 'jaune') || str_contains($m, 'yellow')) {
            return 'jaune';
        }
        return null;
    }
}
