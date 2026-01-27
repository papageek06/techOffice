<?php

namespace App\Enum;

/**
 * Source d'un relevé de compteur (pour facturation)
 */
enum SourceCompteur: string
{
    case MANUEL = 'MANUEL';
    case SNMP = 'SNMP';
    case SCAN = 'SCAN';
}
