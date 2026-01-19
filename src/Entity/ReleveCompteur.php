<?php
// src/Entity/ReleveCompteur.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'releve_compteur')]
class ReleveCompteur
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'relevesCompteur')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Imprimante $imprimante;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateReleve;

    #[ORM\Column(nullable: true)]
    private ?int $compteurNoir = null;

    #[ORM\Column(nullable: true)]
    private ?int $compteurCouleur = null;

    #[ORM\Column(nullable: true)]
    private ?int $compteurFax = null;

    #[ORM\Column(length: 30, options: ['default' => 'manuel'])]
    private string $source = 'manuel';

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

    public function getDateReleve(): \DateTimeImmutable
    {
        return $this->dateReleve;
    }

    public function setDateReleve(\DateTimeImmutable $dateReleve): self
    {
        $this->dateReleve = $dateReleve;
        return $this;
    }

    public function getCompteurNoir(): ?int
    {
        return $this->compteurNoir;
    }

    public function setCompteurNoir(?int $compteurNoir): self
    {
        $this->compteurNoir = $compteurNoir;
        return $this;
    }

    public function getCompteurCouleur(): ?int
    {
        return $this->compteurCouleur;
    }

    public function setCompteurCouleur(?int $compteurCouleur): self
    {
        $this->compteurCouleur = $compteurCouleur;
        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function getCompteurFax(): ?int
    {
        return $this->compteurFax;
    }

    public function setCompteurFax(?int $compteurFax): self
    {
        $this->compteurFax = $compteurFax;
        return $this;
    }
}
