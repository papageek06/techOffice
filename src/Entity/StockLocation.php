<?php

namespace App\Entity;

use App\Enum\StockLocationType;
use App\Repository\StockLocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Emplacement de stock (réutilise la table site pour la localisation)
 * Contrainte unique : (site_id, nom_stock) permet plusieurs stocks sur un même site
 * (ex: "Atelier principal" et "Dépôt secondaire" sur le même site entreprise)
 */
#[ORM\Entity(repositoryClass: StockLocationRepository::class)]
#[ORM\Table(name: 'stock_location')]
#[ORM\UniqueConstraint(name: 'uniq_site_nom_stock', columns: ['site_id', 'nom_stock'])]
#[ORM\Index(name: 'idx_type', columns: ['type'])]
#[ORM\HasLifecycleCallbacks]
class StockLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    #[ORM\Column(enumType: StockLocationType::class)]
    private StockLocationType $type;

    #[ORM\Column(length: 255)]
    private string $nomStock;

    #[ORM\Column(options: ['default' => true])]
    private bool $actif = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, StockItem> */
    #[ORM\OneToMany(mappedBy: 'stockLocation', targetEntity: StockItem::class, orphanRemoval: true)]
    private Collection $stockItems;

    public function __construct()
    {
        $this->stockItems = new ArrayCollection();
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

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(Site $site): self
    {
        $this->site = $site;
        return $this;
    }

    public function getType(): StockLocationType
    {
        return $this->type;
    }

    public function setType(StockLocationType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getNomStock(): string
    {
        return $this->nomStock;
    }

    public function setNomStock(string $nomStock): self
    {
        $this->nomStock = $nomStock;
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
            $stockItem->setStockLocation($this);
        }
        return $this;
    }

    public function removeStockItem(StockItem $stockItem): self
    {
        $this->stockItems->removeElement($stockItem);
        return $this;
    }
}
