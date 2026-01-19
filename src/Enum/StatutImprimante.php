<?php
// src/Enum/StatutImprimante.php
namespace App\Enum;

enum StatutImprimante: string
{
    case ACTIF = 'actif';
    case PRET = 'pret';
    case ASSURANCE = 'assurance';
    case HS = 'hs';
    case DECHETERIE = 'decheterie';
}
