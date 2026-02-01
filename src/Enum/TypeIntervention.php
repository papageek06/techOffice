<?php
// src/Enum/TypeIntervention.php
namespace App\Enum;

enum TypeIntervention: string
{
    case SUR_SITE = 'sur_site';
    case DISTANCE = 'distance';
    case ATELIER = 'atelier';
    case LIVRAISON_TONER = 'livraison_toner';
}
