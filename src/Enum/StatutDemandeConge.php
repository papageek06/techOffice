<?php
// src/Enum/StatutDemandeConge.php
namespace App\Enum;

enum StatutDemandeConge: string
{
    case DEMANDEE = 'demandee';
    case ACCEPTEE = 'acceptee';
    case REFUSEE = 'refusee';
    case ANNULEE = 'annulee';
}
