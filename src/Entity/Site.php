<?php
// src/Entity/Site.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'site')]
class Site
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $client;

    #[ORM\Column(length: 255)]
    private string $nomSite;

    #[ORM\Column(options: ['default' => false])]
    private bool $principal = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $actif = true;

    /** @var Collection<int, Imprimante> */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Imprimante::class, orphanRemoval: true)]
    private Collection $imprimantes;

    public function __construct()
    {
        $this->imprimantes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getNomSite(): string
    {
        return $this->nomSite;
    }

    public function setNomSite(string $nomSite): self
    {
        $this->nomSite = $nomSite;
        return $this;
    }

    public function isPrincipal(): bool
    {
        return $this->principal;
    }

    public function setPrincipal(bool $principal): self
    {
        $this->principal = $principal;
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
            $imprimante->setSite($this);
        }
        return $this;
    }

    public function removeImprimante(Imprimante $imprimante): self
    {
        $this->imprimantes->removeElement($imprimante);
        return $this;
    }
}
