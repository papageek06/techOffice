<?php
// src/Entity/DemandeConge.php
namespace App\Entity;

use App\Enum\StatutDemandeConge;
use App\Enum\TypeConge;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'demande_conge')]
class DemandeConge
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $utilisateur;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column(enumType: TypeConge::class)]
    private TypeConge $typeConge = TypeConge::PAYE;

    #[ORM\Column(enumType: StatutDemandeConge::class)]
    private StatutDemandeConge $statut = StatutDemandeConge::DEMANDEE;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateDemande;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->dateDemande = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTypeConge(): TypeConge
    {
        return $this->typeConge;
    }

    public function setTypeConge(TypeConge $typeConge): self
    {
        $this->typeConge = $typeConge;
        return $this;
    }

    public function getStatut(): StatutDemandeConge
    {
        return $this->statut;
    }

    public function setStatut(StatutDemandeConge $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateDemande(): \DateTimeImmutable
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeImmutable $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }
}
