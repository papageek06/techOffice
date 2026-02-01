<?php

namespace App\Entity;

use App\Repository\StockItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Quantité d'une pièce dans un stock
 */
#[ORM\Entity(repositoryClass: StockItemRepository::class)]
#[ORM\Table(name: 'stock_item')]
#[ORM\UniqueConstraint(name: 'uniq_stock_piece', columns: ['stock_location_id', 'piece_id'])]
#[ORM\Index(name: 'idx_piece_id', columns: ['piece_id'])]
#[ORM\Index(name: 'idx_stock_location_id', columns: ['stock_location_id'])]
#[ORM\HasLifecycleCallbacks]
class StockItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stockItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private StockLocation $stockLocation;

    #[ORM\ManyToOne(inversedBy: 'stockItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Piece $piece;

    #[ORM\Column(options: ['default' => 0])]
    private int $quantite = 0;

    /** Quantité maximum à conserver en stock (pour compléter le stock jusqu'à ce niveau). */
    #[ORM\Column(nullable: true)]
    private ?int $quantiteMax = null;

    #[ORM\Column(nullable: true)]
    private ?int $seuilAlerte = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStockLocation(): StockLocation
    {
        return $this->stockLocation;
    }

    public function setStockLocation(StockLocation $stockLocation): self
    {
        $this->stockLocation = $stockLocation;
        return $this;
    }

    public function getPiece(): Piece
    {
        return $this->piece;
    }

    public function setPiece(Piece $piece): self
    {
        $this->piece = $piece;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getQuantiteMax(): ?int
    {
        return $this->quantiteMax;
    }

    public function setQuantiteMax(?int $quantiteMax): self
    {
        $this->quantiteMax = $quantiteMax;
        return $this;
    }

    public function getSeuilAlerte(): ?int
    {
        return $this->seuilAlerte;
    }

    public function setSeuilAlerte(?int $seuilAlerte): self
    {
        $this->seuilAlerte = $seuilAlerte;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
