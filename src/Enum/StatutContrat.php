<?php

namespace App\Enum;

/**
 * Statut d'un contrat
 */
enum StatutContrat: string
{
    case BROUILLON = 'BROUILLON';
    case ACTIF = 'ACTIF';
    case SUSPENDU = 'SUSPENDU';
    case RESILIE = 'RESILIE';
    case TERMINE = 'TERMINE';
}
