<?php
// src/Entity/Intervention.php
namespace App\Entity;

use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
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

    #[ORM\Column(options: ['default' => true])]
    private bool $facturable = true;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
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

    public function isFacturable(): bool
    {
        return $this->facturable;
    }

    public function setFacturable(bool $facturable): self
    {
        $this->facturable = $facturable;
        return $this;
    }
}
