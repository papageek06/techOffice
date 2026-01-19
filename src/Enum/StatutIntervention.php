<?php
// src/Enum/StatutIntervention.php
namespace App\Enum;

enum StatutIntervention: string
{
    case OUVERTE = 'ouverte';
    case EN_COURS = 'en_cours';
    case TERMINEE = 'terminee';
    case ANNULEE = 'annulee';
}
