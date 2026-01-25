<?php

namespace App\Enum;

/**
 * Rôle d'une pièce pour un modèle d'imprimante (indique quel toner correspond à quelle couleur)
 */
enum PieceRoleModele: string
{
    case TONER_K = 'TONER_K';      // Toner Noir
    case TONER_C = 'TONER_C';      // Toner Cyan
    case TONER_M = 'TONER_M';      // Toner Magenta
    case TONER_Y = 'TONER_Y';      // Toner Jaune
    case BAC_RECUP = 'BAC_RECUP';  // Bac de récupération
    case DRUM = 'DRUM';            // Tambour
    case FUSER = 'FUSER';          // Unité de fusion
    case AUTRE = 'AUTRE';          // Autre pièce
}
