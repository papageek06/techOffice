<?php
// src/Entity/Intervention.php
namespace App\Entity;

use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'intervention')]
class Intervention
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Imprimante $imprimante;

    // utilisateur Symfony (technicien / compta / patron / IT)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $utilisateur;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateIntervention = null;

    #[ORM\Column(enumType: TypeIntervention::class)]
    private TypeIntervention $typeIntervention = TypeIntervention::SUR_SITE;

    #[ORM\Column(enumType: StatutIntervention::class)]
    private StatutIntervention $statut = StatutIntervention::OUVERTE;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column]
    private int $tempsFacturableMinutes = 0; // modifiable

    #[ORM\Column(nullable: true)]
    private ?int $tempsReelMinutes = null;

    /** null = non validé par l'admin, true = à facturer, false = ne pas facturer */
    #[ORM\Column(nullable: true)]
    private ?bool $facturable = null;

    /** True une fois les mouvements de stock appliqués à la clôture (livraison toner). */
    #[ORM\Column(options: ['default' => false])]
    private bool $stockApplique = false;

    /** @var Collection<int, InterventionLigne> */
    #[ORM\OneToMany(mappedBy: 'intervention', targetEntity: InterventionLigne::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignes;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUtilisateur(): User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateIntervention(): ?\DateTimeImmutable
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(?\DateTimeImmutable $dateIntervention): self
    {
        $this->dateIntervention = $dateIntervention;
        return $this;
    }

    public function getTypeIntervention(): TypeIntervention
    {
        return $this->typeIntervention;
    }

    public function setTypeIntervention(TypeIntervention $typeIntervention): self
    {
        $this->typeIntervention = $typeIntervention;
        return $this;
    }

    public function getStatut(): StatutIntervention
    {
        return $this->statut;
    }

    public function setStatut(StatutIntervention $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTempsFacturableMinutes(): int
    {
        return $this->tempsFacturableMinutes;
    }

    public function setTempsFacturableMinutes(int $tempsFacturableMinutes): self
    {
        $this->tempsFacturableMinutes = $tempsFacturableMinutes;
        return $this;
    }

    public function getTempsReelMinutes(): ?int
    {
        return $this->tempsReelMinutes;
    }

    public function setTempsReelMinutes(?int $tempsReelMinutes): self
    {
        $this->tempsReelMinutes = $tempsReelMinutes;
        return $this;
    }

    public function isFacturable(): ?bool
    {
        return $this->facturable;
    }

    public function setFacturable(?bool $facturable): self
    {
        $this->facturable = $facturable;
        return $this;
    }

    public function isStockApplique(): bool
    {
        return $this->stockApplique;
    }

    public function setStockApplique(bool $stockApplique): self
    {
        $this->stockApplique = $stockApplique;
        return $this;
    }

    /**
     * @return Collection<int, InterventionLigne>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(InterventionLigne $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setIntervention($this);
        }
        return $this;
    }

    public function removeLigne(InterventionLigne $ligne): self
    {
        $this->lignes->removeElement($ligne);
        return $this;
    }
}
