<?php

namespace App\Entity;

use App\Enum\PieceType;
use App\Repository\PieceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Catalogue des pièces consommables
 */
#[ORM\Entity(repositoryClass: PieceRepository::class)]
#[ORM\Table(name: 'piece')]
#[ORM\UniqueConstraint(name: 'uniq_piece_reference', columns: ['reference'])]
#[ORM\HasLifecycleCallbacks]
class Piece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    private string $reference;

    #[ORM\Column(length: 255)]
    private string $designation;

    #[ORM\Column(enumType: PieceType::class)]
    private PieceType $typePiece;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $couleur = null; // K, C, M, Y pour les toners

    #[ORM\Column(options: ['default' => true])]
    private bool $actif = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, StockItem> */
    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: StockItem::class, orphanRemoval: true)]
    private Collection $stockItems;

    /** @var Collection<int, PieceModele> */
    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: PieceModele::class, orphanRemoval: true)]
    private Collection $pieceModeles;

    public function __construct()
    {
        $this->stockItems = new ArrayCollection();
        $this->pieceModeles = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getDesignation(): string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;
        return $this;
    }

    public function getTypePiece(): PieceType
    {
        return $this->typePiece;
    }

    public function setTypePiece(PieceType $typePiece): self
    {
        $this->typePiece = $typePiece;
        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
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

    /**
     * @return Collection<int, StockItem>
     */
    public function getStockItems(): Collection
    {
        return $this->stockItems;
    }

    public function addStockItem(StockItem $stockItem): self
    {
        if (!$this->stockItems->contains($stockItem)) {
            $this->stockItems->add($stockItem);
            $stockItem->setPiece($this);
        }
        return $this;
    }

    public function removeStockItem(StockItem $stockItem): self
    {
        $this->stockItems->removeElement($stockItem);
        return $this;
    }

    /**
     * @return Collection<int, PieceModele>
     */
    public function getPieceModeles(): Collection
    {
        return $this->pieceModeles;
    }

    public function addPieceModele(PieceModele $pieceModele): self
    {
        if (!$this->pieceModeles->contains($pieceModele)) {
            $this->pieceModeles->add($pieceModele);
            $pieceModele->setPiece($this);
        }
        return $this;
    }

    public function removePieceModele(PieceModele $pieceModele): self
    {
        $this->pieceModeles->removeElement($pieceModele);
        return $this;
    }
}
