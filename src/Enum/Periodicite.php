<?php

namespace App\Enum;

/**
 * Périodicité de facturation
 */
enum Periodicite: string
{
    case MENSUEL = 'MENSUEL';
    case TRIMESTRIEL = 'TRIMESTRIEL';
    case SEMESTRIEL = 'SEMESTRIEL';
    case ANNUEL = 'ANNUEL';
}
