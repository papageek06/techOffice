<?php

namespace App\Service;

use App\Entity\Piece;
use App\Entity\StockItem;
use App\Entity\StockLocation;
use App\Enum\PieceType;
use App\Enum\StockLocationType;

/**
 * Service de validation pour les stocks
 * Empêche l'ajout de pièces non autorisées dans un stock CLIENT
 */
class StockValidationService
{
    /**
     * Vérifie si une pièce peut être ajoutée à un stock
     * Règle : Les stocks CLIENT ne peuvent contenir que TONER et BAC_RECUP
     */
    public function canAddPieceToStock(Piece $piece, StockLocation $stockLocation): bool
    {
        // Les stocks ENTREPRISE acceptent tous les types de pièces
        if ($stockLocation->getType() === StockLocationType::ENTREPRISE) {
            return true;
        }

        // Les stocks CLIENT n'acceptent que TONER et BAC_RECUP
        if ($stockLocation->getType() === StockLocationType::CLIENT) {
            return in_array($piece->getTypePiece(), [
                PieceType::TONER,
                PieceType::BAC_RECUP,
            ], true);
        }

        return false;
    }

    /**
     * Valide qu'un StockItem respecte les règles de stock
     * Lance une exception si la validation échoue
     */
    public function validateStockItem(StockItem $stockItem): void
    {
        $piece = $stockItem->getPiece();
        $stockLocation = $stockItem->getStockLocation();

        if (!$this->canAddPieceToStock($piece, $stockLocation)) {
            throw new \InvalidArgumentException(sprintf(
                'Les stocks CLIENT ne peuvent contenir que des pièces de type TONER ou BAC_RECUP. '
                . 'Type de pièce reçu: %s',
                $piece->getTypePiece()->value
            ));
        }
    }
}
