<?php

namespace App\Enum;

/**
 * Type de contrat
 */
enum TypeContrat: string
{
    case MAINTENANCE = 'MAINTENANCE';
    case LOCATION = 'LOCATION';
    case VENTE = 'VENTE';
    case PRET = 'PRET';
}
