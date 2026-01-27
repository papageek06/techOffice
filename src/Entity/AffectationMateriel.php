<?php

namespace App\Entity;

use App\Enum\TypeAffectation;
use App\Repository\AffectationMaterielRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Affectation d'une imprimante à une ligne de contrat
 * Contrainte: une seule affectation active (dateFin NULL) par contrat_ligne
 */
#[ORM\Entity(repositoryClass: AffectationMaterielRepository::class)]
#[ORM\Table(name: 'affectation_materiel')]
#[ORM\Index(name: 'idx_contrat_ligne', columns: ['contrat_ligne_id'])]
#[ORM\Index(name: 'idx_imprimante', columns: ['imprimante_id'])]
#[ORM\HasLifecycleCallbacks]
class AffectationMateriel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'affectationsMateriel')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ContratLigne $contratLigne;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Imprimante $imprimante;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(enumType: TypeAffectation::class)]
    private TypeAffectation $typeAffectation = TypeAffectation::PRINCIPALE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, FacturationCompteur> */
    #[ORM\OneToMany(mappedBy: 'affectationMateriel', targetEntity: FacturationCompteur::class, orphanRemoval: true)]
    private Collection $facturationCompteurs;

    public function __construct()
    {
        $this->facturationCompteurs = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getContratLigne(): ContratLigne
    {
        return $this->contratLigne;
    }

    public function setContratLigne(ContratLigne $contratLigne): self
    {
        $this->contratLigne = $contratLigne;
        return $this;
    }

    public function getImprimante(): Imprimante
    {
        return $this->imprimante;
    }

    public function setImprimante(Imprimante $imprimante): self
    {
        $this->imprimante = $imprimante;
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

    public function getTypeAffectation(): TypeAffectation
    {
        return $this->typeAffectation;
    }

    public function setTypeAffectation(TypeAffectation $typeAffectation): self
    {
        $this->typeAffectation = $typeAffectation;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
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
     * @return Collection<int, FacturationCompteur>
     */
    public function getFacturationCompteurs(): Collection
    {
        return $this->facturationCompteurs;
    }

    public function addFacturationCompteur(FacturationCompteur $facturationCompteur): self
    {
        if (!$this->facturationCompteurs->contains($facturationCompteur)) {
            $this->facturationCompteurs->add($facturationCompteur);
            $facturationCompteur->setAffectationMateriel($this);
        }
        return $this;
    }

    public function removeFacturationCompteur(FacturationCompteur $facturationCompteur): self
    {
        $this->facturationCompteurs->removeElement($facturationCompteur);
        return $this;
    }

    /**
     * Vérifie si l'affectation est active (dateFin NULL)
     */
    public function isActive(): bool
    {
        return $this->dateFin === null;
    }
}
