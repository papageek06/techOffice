<?php

namespace App\Service;

use App\Entity\EtatConsommable;
use App\Entity\Imprimante;
use App\Entity\StockItem;
use App\Enum\PieceRoleModele;
use App\Repository\StockItemRepository;

/**
 * Service pour gérer les alertes de stock bas
 */
class TonerAlertService
{
    public function __construct(
        private StockLocatorService $stockLocatorService,
        private TonerCompatibilityService $tonerCompatibilityService,
        private StockItemRepository $stockItemRepository
    ) {
    }

    /**
     * Vérifie si une alerte doit être déclenchée pour une imprimante et un rôle de pièce
     * 
     * @param Imprimante $imprimante L'imprimante à vérifier
     * @param PieceRoleModele $role Le rôle de la pièce (TONER_K, TONER_C, etc.)
     * @param int $percentThreshold Seuil de pourcentage (défaut: 10%)
     * @return bool True si alerte nécessaire
     */
    public function shouldAlertDelivery(
        Imprimante $imprimante,
        PieceRoleModele $role,
        int $percentThreshold = 10
    ): bool {
        // Vérifier que l'imprimante est suivie
        if (!$imprimante->isSuivieParService()) {
            return false;
        }

        // Récupérer le dernier état consommable
        $dernierEtat = $imprimante->getDernierEtatConsommable();
        if (!$dernierEtat) {
            return false; // Pas de données, pas d'alerte
        }

        // Vérifier le pourcentage selon le rôle
        $pourcent = $this->getPourcentForRole($dernierEtat, $role);
        if ($pourcent === null) {
            return false; // Pas de donnée pour ce rôle
        }

        // Alerte si pourcentage <= threshold OU valeur == 0 (Low)
        if ($pourcent > $percentThreshold && $pourcent > 0) {
            return false; // Niveau suffisant
        }

        // Récupérer le stock CLIENT du site
        $stockClient = $this->stockLocatorService->getClientStockForImprimante($imprimante);
        if (!$stockClient) {
            return false; // Pas de stock client, pas d'alerte
        }

        // Récupérer la pièce correspondante
        $modele = $imprimante->getModele();
        $piece = $this->tonerCompatibilityService->getPieceForModeleRole($modele, $role);
        if (!$piece) {
            return false; // Pièce non trouvée, pas d'alerte
        }

        // Récupérer le StockItem
        $stockItem = $this->stockItemRepository->findForStockAndPiece($stockClient, $piece);
        if (!$stockItem) {
            // Pas de StockItem = stock à 0, alerte
            return true;
        }

        // Vérifier le seuil d'alerte
        $seuil = $stockItem->getSeuilAlerte();
        if ($seuil !== null) {
            return $stockItem->getQuantite() <= $seuil;
        }

        // Pas de seuil défini, alerte si stock <= 0
        return $stockItem->getQuantite() <= 0;
    }

    /**
     * Récupère le pourcentage pour un rôle donné depuis l'état consommable
     */
    private function getPourcentForRole(EtatConsommable $etat, PieceRoleModele $role): ?int
    {
        return match ($role) {
            PieceRoleModele::TONER_K => $etat->getNoirPourcent(),
            PieceRoleModele::TONER_C => $etat->getCyanPourcent(),
            PieceRoleModele::TONER_M => $etat->getMagentaPourcent(),
            PieceRoleModele::TONER_Y => $etat->getJaunePourcent(),
            PieceRoleModele::BAC_RECUP => $etat->getBacRecuperation(),
            default => null,
        };
    }
}
