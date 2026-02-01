<?php

namespace App\Service;

use App\Entity\Intervention;
use App\Entity\Piece;
use App\Entity\StockItem;
use App\Entity\StockLocation;
use App\Enum\StatutIntervention;
use App\Repository\StockItemRepository;
use App\Repository\StockLocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use App\Repository\PieceModeleRepository;

/**
 * Quand une intervention avec lignes est terminée :
 * - débit du stock entreprise pour toutes les pièces (livrées ou installées)
 * - crédit du stock du site client uniquement pour toners et bacs récup. (livrés en stock)
 * Les autres pièces (drum, fuser, etc.) sont installées directement, donc pas de crédit client.
 * La date d'intervention est fixée à la date de création à la clôture.
 */
class InterventionClotureStockService
{
    public function __construct(
        private StockLocationRepository $stockLocationRepository,
        private StockItemRepository $stockItemRepository,
        private StockLocatorService $stockLocatorService,
        private PieceModeleRepository $pieceModeleRepository,
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Applique les mouvements de stock pour une intervention terminée (avec lignes).
     * Débit stock entreprise pour toutes les lignes ; crédit stock client seulement pour toner et bac récup.
     */
    public function applyStockLivraison(Intervention $intervention): bool
    {
        if ($intervention->getStatut() !== StatutIntervention::TERMINEE) {
            return false;
        }
        if ($intervention->isStockApplique()) {
            return false;
        }
        if ($intervention->getLignes()->isEmpty()) {
            return false;
        }

        $stockEntrepriseList = $this->stockLocationRepository->findEntrepriseStocks();
        $stockEntreprise = $stockEntrepriseList[0] ?? null;
        if (!$stockEntreprise) {
            if ($this->logger) {
                $this->logger->warning('InterventionClotureStockService: aucun stock entreprise trouvé.');
            }
            return false;
        }

        $stockClient = $this->stockLocatorService->getClientStockForImprimante($intervention->getImprimante());
        $modele = $intervention->getImprimante()->getModele();

        foreach ($intervention->getLignes() as $ligne) {
            $piece = $ligne->getPiece();
            $quantite = $ligne->getQuantite();
            if ($quantite <= 0) {
                continue;
            }

            $this->debiterStockEntreprise($stockEntreprise, $piece, $quantite);

            $pieceModele = $this->pieceModeleRepository->findOneBy(['piece' => $piece, 'modele' => $modele]);
            if ($pieceModele && $stockClient) {
                $role = $pieceModele->getRole()->value;
                $isTonerOuBac = str_starts_with($role, 'TONER_') || $role === 'BAC_RECUP';
                if ($isTonerOuBac) {
                    $this->crediterStockClient($stockClient, $piece, $quantite);
                }
            }
        }

        $intervention->setStockApplique(true);
        $this->entityManager->flush();

        return true;
    }

    private function debiterStockEntreprise(StockLocation $stockLocation, Piece $piece, int $quantite): void
    {
        $stockItem = $this->stockItemRepository->findForStockAndPiece($stockLocation, $piece);
        if ($stockItem === null) {
            $stockItem = new StockItem();
            $stockItem->setStockLocation($stockLocation);
            $stockItem->setPiece($piece);
            $stockItem->setQuantite(-$quantite);
            $this->entityManager->persist($stockItem);
        } else {
            $stockItem->setQuantite($stockItem->getQuantite() - $quantite);
        }
    }

    private function crediterStockClient(StockLocation $stockLocation, Piece $piece, int $quantite): void
    {
        $stockItem = $this->stockItemRepository->findForStockAndPiece($stockLocation, $piece);
        if ($stockItem === null) {
            $stockItem = new StockItem();
            $stockItem->setStockLocation($stockLocation);
            $stockItem->setPiece($piece);
            $stockItem->setQuantite($quantite);
            $this->entityManager->persist($stockItem);
        } else {
            $stockItem->setQuantite($stockItem->getQuantite() + $quantite);
        }
    }
}
