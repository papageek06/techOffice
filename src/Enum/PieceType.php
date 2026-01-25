<?php

namespace App\Enum;

/**
 * Type de pièce consommable
 */
enum PieceType: string
{
    case TONER = 'TONER';
    case BAC_RECUP = 'BAC_RECUP';
    case DRUM = 'DRUM';
    case FUSER = 'FUSER';
    case MAINTENANCE_KIT = 'MAINTENANCE_KIT';
    case AUTRE = 'AUTRE';
}
