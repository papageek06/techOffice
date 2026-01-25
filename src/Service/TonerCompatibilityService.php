<?php

namespace App\Service;

use App\Entity\Modele;
use App\Entity\Piece;
use App\Enum\PieceRoleModele;
use App\Repository\PieceModeleRepository;

/**
 * Service pour gérer la compatibilité des pièces avec les modèles
 */
class TonerCompatibilityService
{
    public function __construct(
        private PieceModeleRepository $pieceModeleRepository
    ) {
    }

    /**
     * Retourne la pièce correspondant à un modèle et un rôle
     */
    public function getPieceForModeleRole(Modele $modele, PieceRoleModele $role): ?Piece
    {
        return $this->pieceModeleRepository->findPieceForModeleAndRole($modele, $role);
    }
}
