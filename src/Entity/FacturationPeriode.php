<?php

namespace App\Entity;

use App\Enum\StatutFacturation;
use App\Repository\FacturationPeriodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Période de facturation pour une ligne de contrat
 */
#[ORM\Entity(repositoryClass: FacturationPeriodeRepository::class)]
#[ORM\Table(name: 'facturation_periode')]
#[ORM\Index(name: 'idx_fact_periode_contrat_ligne', columns: ['contrat_ligne_id'])]
#[ORM\Index(name: 'idx_statut', columns: ['statut'])]
#[ORM\HasLifecycleCallbacks]
class FacturationPeriode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'facturationPeriodes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ContratLigne $contratLigne;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column(enumType: StatutFacturation::class)]
    private StatutFacturation $statut = StatutFacturation::BROUILLON;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, FacturationCompteur> */
    #[ORM\OneToMany(mappedBy: 'facturationPeriode', targetEntity: FacturationCompteur::class, orphanRemoval: true)]
    private Collection $facturationCompteurs;

    public function __construct()
    {
        $this->facturationCompteurs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getDateDebut(): \DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): \DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeImmutable $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): StatutFacturation
    {
        return $this->statut;
    }

    public function setStatut(StatutFacturation $statut): self
    {
        $this->statut = $statut;
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
            $facturationCompteur->setFacturationPeriode($this);
        }
        return $this;
    }

    public function removeFacturationCompteur(FacturationCompteur $facturationCompteur): self
    {
        $this->facturationCompteurs->removeElement($facturationCompteur);
        return $this;
    }
}
