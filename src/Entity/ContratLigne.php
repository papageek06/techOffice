<?php

namespace App\Entity;

use App\Enum\Periodicite;
use App\Repository\ContratLigneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ligne de contrat (une ligne par site)
 */
#[ORM\Entity(repositoryClass: ContratLigneRepository::class)]
#[ORM\Table(name: 'contrat_ligne')]
#[ORM\Index(name: 'idx_prochaine_facturation', columns: ['prochaine_facturation'])]
#[ORM\HasLifecycleCallbacks]
class ContratLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contratLignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Contrat $contrat;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Site $site;

    #[ORM\Column(length: 255)]
    private string $libelle;

    #[ORM\Column(enumType: Periodicite::class)]
    private Periodicite $periodicite;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $prochaineFacturation;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixFixe = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $prixPageNoir = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $prixPageCouleur = null;

    #[ORM\Column(nullable: true)]
    private ?int $pagesInclusesNoir = null;

    #[ORM\Column(nullable: true)]
    private ?int $pagesInclusesCouleur = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $actif = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, AffectationMateriel> */
    #[ORM\OneToMany(mappedBy: 'contratLigne', targetEntity: AffectationMateriel::class, orphanRemoval: true)]
    private Collection $affectationsMateriel;

    /** @var Collection<int, FacturationPeriode> */
    #[ORM\OneToMany(mappedBy: 'contratLigne', targetEntity: FacturationPeriode::class, orphanRemoval: true)]
    private Collection $facturationPeriodes;

    public function __construct()
    {
        $this->affectationsMateriel = new ArrayCollection();
        $this->facturationPeriodes = new ArrayCollection();
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

    public function getContrat(): Contrat
    {
        return $this->contrat;
    }

    public function setContrat(Contrat $contrat): self
    {
        $this->contrat = $contrat;
        return $this;
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

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getPeriodicite(): Periodicite
    {
        return $this->periodicite;
    }

    public function setPeriodicite(Periodicite $periodicite): self
    {
        $this->periodicite = $periodicite;
        return $this;
    }

    public function getProchaineFacturation(): \DateTimeImmutable
    {
        return $this->prochaineFacturation;
    }

    public function setProchaineFacturation(\DateTimeImmutable $prochaineFacturation): self
    {
        $this->prochaineFacturation = $prochaineFacturation;
        return $this;
    }

    public function getPrixFixe(): ?string
    {
        return $this->prixFixe;
    }

    public function setPrixFixe(?string $prixFixe): self
    {
        $this->prixFixe = $prixFixe;
        return $this;
    }

    public function getPrixPageNoir(): ?string
    {
        return $this->prixPageNoir;
    }

    public function setPrixPageNoir(?string $prixPageNoir): self
    {
        $this->prixPageNoir = $prixPageNoir;
        return $this;
    }

    public function getPrixPageCouleur(): ?string
    {
        return $this->prixPageCouleur;
    }

    public function setPrixPageCouleur(?string $prixPageCouleur): self
    {
        $this->prixPageCouleur = $prixPageCouleur;
        return $this;
    }

    public function getPagesInclusesNoir(): ?int
    {
        return $this->pagesInclusesNoir;
    }

    public function setPagesInclusesNoir(?int $pagesInclusesNoir): self
    {
        $this->pagesInclusesNoir = $pagesInclusesNoir;
        return $this;
    }

    public function getPagesInclusesCouleur(): ?int
    {
        return $this->pagesInclusesCouleur;
    }

    public function setPagesInclusesCouleur(?int $pagesInclusesCouleur): self
    {
        $this->pagesInclusesCouleur = $pagesInclusesCouleur;
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
     * @return Collection<int, AffectationMateriel>
     */
    public function getAffectationsMateriel(): Collection
    {
        return $this->affectationsMateriel;
    }

    public function addAffectationMateriel(AffectationMateriel $affectationMateriel): self
    {
        if (!$this->affectationsMateriel->contains($affectationMateriel)) {
            $this->affectationsMateriel->add($affectationMateriel);
            $affectationMateriel->setContratLigne($this);
        }
        return $this;
    }

    public function removeAffectationMateriel(AffectationMateriel $affectationMateriel): self
    {
        $this->affectationsMateriel->removeElement($affectationMateriel);
        return $this;
    }

    /**
     * @return Collection<int, FacturationPeriode>
     */
    public function getFacturationPeriodes(): Collection
    {
        return $this->facturationPeriodes;
    }

    public function addFacturationPeriode(FacturationPeriode $facturationPeriode): self
    {
        if (!$this->facturationPeriodes->contains($facturationPeriode)) {
            $this->facturationPeriodes->add($facturationPeriode);
            $facturationPeriode->setContratLigne($this);
        }
        return $this;
    }

    public function removeFacturationPeriode(FacturationPeriode $facturationPeriode): self
    {
        $this->facturationPeriodes->removeElement($facturationPeriode);
        return $this;
    }

    /**
     * Récupère l'affectation active (dateFin NULL)
     */
    public function getAffectationActive(): ?AffectationMateriel
    {
        foreach ($this->affectationsMateriel as $affectation) {
            if ($affectation->getDateFin() === null) {
                return $affectation;
            }
        }
        return null;
    }
}
