<?php

namespace App\Service;

use App\Entity\Modele;
use App\Entity\Piece;
use App\Entity\StockItem;
use App\Entity\StockLocation;
use App\Repository\PieceModeleRepository;
use App\Repository\StockItemRepository;
use App\Repository\StockLocationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Pour chaque site (lors de l'import CSV ou création), on vérifie le modèle de l'imprimante
 * et les pièces compatibles : on règle le stock entreprise avec quantite à 0 par défaut
 * et quantiteMax à 1 pour chaque pièce liée au modèle.
 */
class ReglerStockEntrepriseService
{
    private const QUANTITE_DEFAUT = 0;
    private const QUANTITE_MAX_DEFAUT = 1;

    public function __construct(
        private PieceModeleRepository $pieceModeleRepository,
        private StockLocationRepository $stockLocationRepository,
        private StockItemRepository $stockItemRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Règle les stocks entreprise pour le modèle donné : crée ou met à jour les StockItem
     * pour chaque pièce compatible avec le modèle (quantite = 0, quantiteMax = 1).
     */
    public function reglerStockEntreprisePourModele(Modele $modele): void
    {
        $pieceModeles = $this->pieceModeleRepository->findPiecesForModele($modele);
        if ($pieceModeles === []) {
            return;
        }

        $stocksEntreprise = $this->stockLocationRepository->findEntrepriseStocks();
        if ($stocksEntreprise === []) {
            return;
        }

        foreach ($stocksEntreprise as $stockLocation) {
            foreach ($pieceModeles as $pieceModele) {
                $piece = $pieceModele->getPiece();
                $this->ensureStockItem($stockLocation, $piece);
            }
        }
    }

    private function ensureStockItem(StockLocation $stockLocation, Piece $piece): void
    {
        $stockItem = $this->stockItemRepository->findForStockAndPiece($stockLocation, $piece);

        if ($stockItem === null) {
            $stockItem = new StockItem();
            $stockItem->setStockLocation($stockLocation);
            $stockItem->setPiece($piece);
            $stockItem->setQuantite(self::QUANTITE_DEFAUT);
            $stockItem->setQuantiteMax(self::QUANTITE_MAX_DEFAUT);
            $this->entityManager->persist($stockItem);
        } else {
            $stockItem->setQuantiteMax(self::QUANTITE_MAX_DEFAUT);
        }
    }
}
