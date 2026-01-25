<?php

namespace App\Enum;

/**
 * Type de stock : ENTREPRISE (atelier, dépôt) ou CLIENT (sur site client)
 */
enum StockLocationType: string
{
    case ENTREPRISE = 'ENTREPRISE';
    case CLIENT = 'CLIENT';
}
