<?php

namespace App\Service;

use App\Entity\Imprimante;
use App\Entity\StockLocation;
use App\Repository\StockLocationRepository;

/**
 * Service pour localiser les stocks
 */
class StockLocatorService
{
    public function __construct(
        private StockLocationRepository $stockLocationRepository
    ) {
    }

    /**
     * Retourne le stock CLIENT du site de l'imprimante (si existe)
     */
    public function getClientStockForImprimante(Imprimante $imprimante): ?StockLocation
    {
        $site = $imprimante->getSite();
        return $this->stockLocationRepository->findClientStockForSite($site);
    }
}
