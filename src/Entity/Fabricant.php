<?php
// src/Entity/Fabricant.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fabricant')]
class Fabricant
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    private string $nomFabricant;

    /** @var Collection<int, Modele> */
    #[ORM\OneToMany(mappedBy: 'fabricant', targetEntity: Modele::class, orphanRemoval: true)]
    private Collection $modeles;

    public function __construct()
    {
        $this->modeles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomFabricant(): string
    {
        return $this->nomFabricant;
    }

    public function setNomFabricant(string $nomFabricant): self
    {
        $this->nomFabricant = $nomFabricant;
        return $this;
    }

    /**
     * @return Collection<int, Modele>
     */
    public function getModeles(): Collection
    {
        return $this->modeles;
    }

    public function addModele(Modele $modele): self
    {
        if (!$this->modeles->contains($modele)) {
            $this->modeles->add($modele);
            $modele->setFabricant($this);
        }
        return $this;
    }

    public function removeModele(Modele $modele): self
    {
        $this->modeles->removeElement($modele);
        return $this;
    }
}
