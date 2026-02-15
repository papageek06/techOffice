<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Imprimante;
use App\Enum\PieceRoleModele;
use App\Repository\StockItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Déduit 1 toner du stock du site lorsqu'une alerte "changement de cartouche" est enregistrée.
 */
final class DeductTonerForAlertService
{
    private const COULEUR_TO_ROLE = [
        'noir' => PieceRoleModele::TONER_K,
        'black' => PieceRoleModele::TONER_K,
        'cyan' => PieceRoleModele::TONER_C,
        'magenta' => PieceRoleModele::TONER_M,
        'jaune' => PieceRoleModele::TONER_Y,
        'yellow' => PieceRoleModele::TONER_Y,
    ];

    public function __construct(
        private readonly TonerCompatibilityService $tonerCompatibilityService,
        private readonly StockLocatorService $stockLocatorService,
        private readonly StockItemRepository $stockItemRepository,
        private readonly EntityManagerInterface $em,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Déduit 1 unité du toner correspondant (couleur) du stock client du site de l'imprimante.
     * Retourne true si la déduction a été faite, false sinon (imprimante non suivie, stock absent, etc.).
     */
    public function deductOneTonerForImprimante(Imprimante $imprimante, string $couleurToner): bool
    {
        $couleur = mb_strtolower(trim($couleurToner));
        $role = self::COULEUR_TO_ROLE[$couleur] ?? null;
        if ($role === null) {
            $this->logger?->warning('DeductTonerForAlert: couleur inconnue', ['couleur' => $couleurToner]);
            return false;
        }

        if (!$imprimante->isSuivieParService()) {
            return false;
        }

        $modele = $imprimante->getModele();
        $piece = $this->tonerCompatibilityService->getPieceForModeleRole($modele, $role);
        if ($piece === null) {
            $this->logger?->info('DeductTonerForAlert: pas de pièce pour modèle/role', [
                'imprimanteId' => $imprimante->getId(),
                'role' => $role->value,
            ]);
            return false;
        }

        $stockClient = $this->stockLocatorService->getClientStockForImprimante($imprimante);
        if ($stockClient === null) {
            $this->logger?->info('DeductTonerForAlert: pas de stock client pour le site');
            return false;
        }

        $stockItem = $this->stockItemRepository->findForStockAndPiece($stockClient, $piece);
        if ($stockItem === null) {
            $this->logger?->info('DeductTonerForAlert: pas de StockItem pour ce toner sur le site');
            return false;
        }

        $qte = $stockItem->getQuantite();
        if ($qte <= 0) {
            $this->logger?->info('DeductTonerForAlert: stock déjà à 0, pas de déduction');
            return false;
        }

        $stockItem->setQuantite($qte - 1);
        $this->em->flush();

        $this->logger?->info('DeductTonerForAlert: 1 toner déduit (changement cartouche)', [
            'imprimanteId' => $imprimante->getId(),
            'pieceId' => $piece->getId(),
            'siteId' => $imprimante->getSite()->getId(),
            'quantiteAvant' => $qte,
        ]);

        return true;
    }
}
