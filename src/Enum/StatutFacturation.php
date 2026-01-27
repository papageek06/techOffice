<?php

namespace App\Enum;

/**
 * Statut d'une période de facturation
 */
enum StatutFacturation: string
{
    case BROUILLON = 'BROUILLON';
    case VALIDE = 'VALIDE';
    case FACTURE = 'FACTURE';
}
