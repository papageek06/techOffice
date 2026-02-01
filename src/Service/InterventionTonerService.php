<?php

namespace App\Service;

use App\Entity\EtatConsommable;
use App\Entity\Imprimante;
use App\Entity\Intervention;
use App\Entity\InterventionLigne;
use App\Entity\Piece;
use App\Enum\PieceRoleModele;
use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use App\Repository\StockItemRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Crée une intervention "livraison toner" lorsque :
 * - Un rapport CSV est reçu (état consommable à jour),
 * - Un niveau d'encre est <= 20%,
 * - Le stock du site n'a pas de quantité > 0 du toner lié à l'imprimante et de la bonne couleur.
 * Liste les pièces à livrer pour compléter le stock du site (quantité cible = 1 par pièce manquante).
 */
class InterventionTonerService
{
    private const SEUIL_NIVEAU_POURCENT = 20;

    public function __construct(
        private StockLocatorService $stockLocatorService,
        private TonerCompatibilityService $tonerCompatibilityService,
        private StockItemRepository $stockItemRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Vérifie les niveaux d'encre de l'état consommable (après réception CSV),
     * le stock du site, et crée une intervention livraison toner si nécessaire.
     */
    public function checkAndCreateInterventionLivraisonToner(Imprimante $imprimante, EtatConsommable $etatConsommable): ?Intervention
    {
        if (!$imprimante->isSuivieParService()) {
            return null;
        }

        $site = $imprimante->getSite();
        $stockClient = $this->stockLocatorService->getClientStockForImprimante($imprimante);
        if (!$stockClient) {
            return null;
        }

        $modele = $imprimante->getModele();
        $piecesALivrer = []; // [Piece => quantité à livrer]

        $rolesToCheck = [
            ['role' => PieceRoleModele::TONER_K, 'pourcent' => $etatConsommable->getNoirPourcent()],
            ['role' => PieceRoleModele::TONER_C, 'pourcent' => $etatConsommable->getCyanPourcent()],
            ['role' => PieceRoleModele::TONER_M, 'pourcent' => $etatConsommable->getMagentaPourcent()],
            ['role' => PieceRoleModele::TONER_Y, 'pourcent' => $etatConsommable->getJaunePourcent()],
        ];

        foreach ($rolesToCheck as $item) {
            $pourcent = $item['pourcent'];
            if ($pourcent === null || $pourcent > self::SEUIL_NIVEAU_POURCENT) {
                continue;
            }

            $piece = $this->tonerCompatibilityService->getPieceForModeleRole($modele, $item['role']);
            if (!$piece) {
                continue;
            }

            $stockItem = $this->stockItemRepository->findForStockAndPiece($stockClient, $piece);
            $quantiteEnStock = $stockItem !== null ? $stockItem->getQuantite() : 0;

            $cibleMax = $stockItem?->getQuantiteMax() ?? 1;
            if ($quantiteEnStock >= $cibleMax) {
                continue;
            }

            $quantiteSouhaitee = $this->quantiteACommander($stockItem, $cibleMax);
            $piecesALivrer[$piece->getId()] = ['piece' => $piece, 'quantite' => $quantiteSouhaitee];
        }

        if ($piecesALivrer === []) {
            return null;
        }

        $utilisateur = $this->userRepository->findFirstAdmin();
        if (!$utilisateur) {
            if ($this->logger) {
                $this->logger->warning('InterventionTonerService: aucun utilisateur admin trouvé, intervention non créée.');
            }
            return null;
        }

        $description = $this->buildDescriptionLivraison($site->getNomSite(), $piecesALivrer);

        $intervention = new Intervention();
        $intervention->setImprimante($imprimante);
        $intervention->setUtilisateur($utilisateur);
        $intervention->setTypeIntervention(TypeIntervention::LIVRAISON_TONER);
        $intervention->setStatut(StatutIntervention::OUVERTE);
        $intervention->setDescription($description);
        $intervention->setTempsFacturableMinutes(0);
        $intervention->setFacturable(false);

        foreach ($piecesALivrer as $item) {
            $ligne = new InterventionLigne();
            $ligne->setPiece($item['piece']);
            $ligne->setQuantite($item['quantite']);
            $intervention->addLigne($ligne);
        }

        $this->entityManager->persist($intervention);

        return $intervention;
    }

    /**
     * Quantité à commander pour compléter le stock (par défaut 1 si pas de seuil max).
     */
    private function quantiteACommander(?\App\Entity\StockItem $stockItem, int $cibleMin): int
    {
        $actuel = $stockItem !== null ? $stockItem->getQuantite() : 0;
        $manquant = max(0, $cibleMin - $actuel);

        return $manquant > 0 ? $manquant : 1;
    }

    private function buildDescriptionLivraison(string $nomSite, array $piecesALivrer): string
    {
        $lignes = [
            sprintf('Livraison toner pour le site "%s".', $nomSite),
            'Pièces à livrer pour compléter le stock :',
        ];
        foreach ($piecesALivrer as $item) {
            $piece = $item['piece'];
            $qte = $item['quantite'];
            $lignes[] = sprintf('  - %s (%s) : %d', $piece->getDesignation(), $piece->getReference(), $qte);
        }

        return implode("\n", $lignes);
    }
}
