<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Type de défaut extrait des Smart Alerts (Katun/PrintAudit).
 */
enum TypeDefautAlert: string
{
    case NIVEAU_ENCRE = 'niveau_encre';           // Toner bas, X % restants
    case CHANGEMENT_CARTOUCHE = 'changement_cartouche'; // Cartouche changée 0% → 100%
    case SPILLAGE = 'spillage';                    // Caspillage
    case AUTRE = 'autre';
}
