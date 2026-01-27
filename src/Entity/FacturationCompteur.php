<?php

namespace App\Entity;

use App\Enum\SourceCompteur;
use App\Repository\FacturationCompteurRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Compteurs de début et fin pour une affectation dans une période de facturation
 */
#[ORM\Entity(repositoryClass: FacturationCompteurRepository::class)]
#[ORM\Table(name: 'facturation_compteur')]
#[ORM\Index(name: 'idx_facturation_periode', columns: ['facturation_periode_id'])]
#[ORM\Index(name: 'idx_affectation_materiel', columns: ['affectation_materiel_id'])]
class FacturationCompteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'facturationCompteurs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private FacturationPeriode $facturationPeriode;

    #[ORM\ManyToOne(inversedBy: 'facturationCompteurs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AffectationMateriel $affectationMateriel;

    #[ORM\Column]
    private int $compteurDebutNoir;

    #[ORM\Column]
    private int $compteurFinNoir;

    #[ORM\Column(nullable: true)]
    private ?int $compteurDebutCouleur = null;

    #[ORM\Column(nullable: true)]
    private ?int $compteurFinCouleur = null;

    #[ORM\Column(enumType: SourceCompteur::class)]
    private SourceCompteur $sourceDebut;

    #[ORM\Column(enumType: SourceCompteur::class)]
    private SourceCompteur $sourceFin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacturationPeriode(): FacturationPeriode
    {
        return $this->facturationPeriode;
    }

    public function setFacturationPeriode(FacturationPeriode $facturationPeriode): self
    {
        $this->facturationPeriode = $facturationPeriode;
        return $this;
    }

    public function getAffectationMateriel(): AffectationMateriel
    {
        return $this->affectationMateriel;
    }

    public function setAffectationMateriel(AffectationMateriel $affectationMateriel): self
    {
        $this->affectationMateriel = $affectationMateriel;
        return $this;
    }

    public function getCompteurDebutNoir(): int
    {
        return $this->compteurDebutNoir;
    }

    public function setCompteurDebutNoir(int $compteurDebutNoir): self
    {
        $this->compteurDebutNoir = $compteurDebutNoir;
        return $this;
    }

    public function getCompteurFinNoir(): int
    {
        return $this->compteurFinNoir;
    }

    public function setCompteurFinNoir(int $compteurFinNoir): self
    {
        $this->compteurFinNoir = $compteurFinNoir;
        return $this;
    }

    public function getCompteurDebutCouleur(): ?int
    {
        return $this->compteurDebutCouleur;
    }

    public function setCompteurDebutCouleur(?int $compteurDebutCouleur): self
    {
        $this->compteurDebutCouleur = $compteurDebutCouleur;
        return $this;
    }

    public function getCompteurFinCouleur(): ?int
    {
        return $this->compteurFinCouleur;
    }

    public function setCompteurFinCouleur(?int $compteurFinCouleur): self
    {
        $this->compteurFinCouleur = $compteurFinCouleur;
        return $this;
    }

    public function getSourceDebut(): SourceCompteur
    {
        return $this->sourceDebut;
    }

    public function setSourceDebut(SourceCompteur $sourceDebut): self
    {
        $this->sourceDebut = $sourceDebut;
        return $this;
    }

    public function getSourceFin(): SourceCompteur
    {
        return $this->sourceFin;
    }

    public function setSourceFin(SourceCompteur $sourceFin): self
    {
        $this->sourceFin = $sourceFin;
        return $this;
    }

    /**
     * Calcule le nombre de pages noires consommées
     */
    public function getPagesNoir(): int
    {
        return max(0, $this->compteurFinNoir - $this->compteurDebutNoir);
    }

    /**
     * Calcule le nombre de pages couleur consommées
     */
    public function getPagesCouleur(): int
    {
        if ($this->compteurDebutCouleur === null || $this->compteurFinCouleur === null) {
            return 0;
        }
        return max(0, $this->compteurFinCouleur - $this->compteurDebutCouleur);
    }
}
