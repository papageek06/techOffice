<?php
// src/Entity/Modele.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'modele')]
#[ORM\UniqueConstraint(name: 'uniq_modele_fabricant_ref', columns: ['fabricant_id', 'reference_modele'])]
class Modele
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'modeles')]
    #[ORM\JoinColumn(nullable: false)]
    private Fabricant $fabricant;

    #[ORM\Column(length: 150)]
    private string $referenceModele;

    #[ORM\Column(options: ['default' => false])]
    private bool $couleur = false;

    /** @var Collection<int, Imprimante> */
    #[ORM\OneToMany(mappedBy: 'modele', targetEntity: Imprimante::class)]
    private Collection $imprimantes;

    public function __construct()
    {
        $this->imprimantes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFabricant(): Fabricant
    {
        return $this->fabricant;
    }

    public function setFabricant(Fabricant $fabricant): self
    {
        $this->fabricant = $fabricant;
        return $this;
    }

    public function getReferenceModele(): string
    {
        return $this->referenceModele;
    }

    public function setReferenceModele(string $referenceModele): self
    {
        $this->referenceModele = $referenceModele;
        return $this;
    }

    public function isCouleur(): bool
    {
        return $this->couleur;
    }

    public function setCouleur(bool $couleur): self
    {
        $this->couleur = $couleur;
        return $this;
    }

    /**
     * @return Collection<int, Imprimante>
     */
    public function getImprimantes(): Collection
    {
        return $this->imprimantes;
    }

    public function addImprimante(Imprimante $imprimante): self
    {
        if (!$this->imprimantes->contains($imprimante)) {
            $this->imprimantes->add($imprimante);
            $imprimante->setModele($this);
        }
        return $this;
    }

    public function removeImprimante(Imprimante $imprimante): self
    {
        $this->imprimantes->removeElement($imprimante);
        return $this;
    }
}
