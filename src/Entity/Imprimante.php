<?php
// src/Entity/Imprimante.php
namespace App\Entity;

use App\Enum\StatutImprimante;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'imprimante')]
#[ORM\UniqueConstraint(name: 'uniq_imprimante_numero_serie', columns: ['numero_serie'])]
class Imprimante
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'imprimantes')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    #[ORM\ManyToOne(inversedBy: 'imprimantes')]
    #[ORM\JoinColumn(nullable: false)]
    private Modele $modele;

    #[ORM\Column(length: 80)]
    private string $numeroSerie;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateInstallation = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $adresseIp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $suivieParService = true;

    #[ORM\Column(enumType: StatutImprimante::class, options: ['default' => 'actif'])]
    private StatutImprimante $statut = StatutImprimante::ACTIF;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /** @var Collection<int, Intervention> */
    #[ORM\OneToMany(mappedBy: 'imprimante', targetEntity: Intervention::class, orphanRemoval: true)]
    private Collection $interventions;

    /** @var Collection<int, ReleveCompteur> */
    #[ORM\OneToMany(mappedBy: 'imprimante', targetEntity: ReleveCompteur::class, orphanRemoval: true)]
    private Collection $relevesCompteur;

    /** @var Collection<int, EtatConsommable> */
    #[ORM\OneToMany(mappedBy: 'imprimante', targetEntity: EtatConsommable::class, orphanRemoval: true)]
    private Collection $etatsConsommable;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->relevesCompteur = new ArrayCollection();
        $this->etatsConsommable = new ArrayCollection();
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

    public function getModele(): Modele
    {
        return $this->modele;
    }

    public function setModele(Modele $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    public function getNumeroSerie(): string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(string $numeroSerie): self
    {
        $this->numeroSerie = $numeroSerie;
        return $this;
    }

    public function getDateInstallation(): ?\DateTimeImmutable
    {
        return $this->dateInstallation;
    }

    public function setDateInstallation(?\DateTimeImmutable $dateInstallation): self
    {
        $this->dateInstallation = $dateInstallation;
        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): self
    {
        $this->adresseIp = $adresseIp;
        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): self
    {
        $this->emplacement = $emplacement;
        return $this;
    }

    public function isSuivieParService(): bool
    {
        return $this->suivieParService;
    }

    public function setSuivieParService(bool $suivieParService): self
    {
        $this->suivieParService = $suivieParService;
        return $this;
    }

    public function getStatut(): StatutImprimante
    {
        return $this->statut;
    }

    public function setStatut(StatutImprimante $statut): self
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

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): self
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setImprimante($this);
        }
        return $this;
    }

    public function removeIntervention(Intervention $intervention): self
    {
        $this->interventions->removeElement($intervention);
        return $this;
    }

    /**
     * @return Collection<int, ReleveCompteur>
     */
    public function getRelevesCompteur(): Collection
    {
        return $this->relevesCompteur;
    }

    public function addReleveCompteur(ReleveCompteur $releveCompteur): self
    {
        if (!$this->relevesCompteur->contains($releveCompteur)) {
            $this->relevesCompteur->add($releveCompteur);
            $releveCompteur->setImprimante($this);
        }
        return $this;
    }

    public function removeReleveCompteur(ReleveCompteur $releveCompteur): self
    {
        $this->relevesCompteur->removeElement($releveCompteur);
        return $this;
    }

    /**
     * @return Collection<int, EtatConsommable>
     */
    public function getEtatsConsommable(): Collection
    {
        return $this->etatsConsommable;
    }

    public function addEtatConsommable(EtatConsommable $etatConsommable): self
    {
        if (!$this->etatsConsommable->contains($etatConsommable)) {
            $this->etatsConsommable->add($etatConsommable);
            $etatConsommable->setImprimante($this);
        }
        return $this;
    }

    public function removeEtatConsommable(EtatConsommable $etatConsommable): self
    {
        $this->etatsConsommable->removeElement($etatConsommable);
        return $this;
    }

    /**
     * Retourne le dernier relevé de compteur
     */
    public function getDernierReleve(): ?ReleveCompteur
    {
        if ($this->relevesCompteur->isEmpty()) {
            return null;
        }

        $dernier = null;
        foreach ($this->relevesCompteur as $releve) {
            if ($dernier === null || $releve->getDateReleve() > $dernier->getDateReleve()) {
                $dernier = $releve;
            }
        }

        return $dernier;
    }

    /**
     * Retourne le dernier état consommable (niveaux d'encre)
     */
    public function getDernierEtatConsommable(): ?EtatConsommable
    {
        if ($this->etatsConsommable->isEmpty()) {
            return null;
        }

        $dernier = null;
        foreach ($this->etatsConsommable as $etat) {
            if ($dernier === null || $etat->getDateCapture() > $dernier->getDateCapture()) {
                $dernier = $etat;
            }
        }

        return $dernier;
    }
}
