<?php

namespace App\Entity;

use App\Enum\StatutContrat;
use App\Enum\TypeContrat;
use App\Repository\ContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contrat client
 */
#[ORM\Entity(repositoryClass: ContratRepository::class)]
#[ORM\Table(name: 'contrat')]
#[ORM\HasLifecycleCallbacks]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Client $client;

    #[ORM\Column(length: 100)]
    private string $reference;

    #[ORM\Column(enumType: TypeContrat::class)]
    private TypeContrat $typeContrat;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(enumType: StatutContrat::class)]
    private StatutContrat $statut = StatutContrat::BROUILLON;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ContratLigne> */
    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: ContratLigne::class, orphanRemoval: true)]
    private Collection $contratLignes;

    public function __construct()
    {
        $this->contratLignes = new ArrayCollection();
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

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
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

    public function getTypeContrat(): TypeContrat
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(TypeContrat $typeContrat): self
    {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    public function getDateDebut(): \DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): StatutContrat
    {
        return $this->statut;
    }

    public function setStatut(StatutContrat $statut): self
    {
        $this->statut = $statut;
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
     * @return Collection<int, ContratLigne>
     */
    public function getContratLignes(): Collection
    {
        return $this->contratLignes;
    }

    public function addContratLigne(ContratLigne $contratLigne): self
    {
        if (!$this->contratLignes->contains($contratLigne)) {
            $this->contratLignes->add($contratLigne);
            $contratLigne->setContrat($this);
        }
        return $this;
    }

    public function removeContratLigne(ContratLigne $contratLigne): self
    {
        $this->contratLignes->removeElement($contratLigne);
        return $this;
    }
}
