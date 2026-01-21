<?php
// src/Entity/EtatConsommable.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'etat_consommable')]
class EtatConsommable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'etatsConsommable')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Imprimante $imprimante;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateCapture;

    #[ORM\Column(nullable: true)]
    private ?int $noirPourcent = null;

    #[ORM\Column(nullable: true)]
    private ?int $cyanPourcent = null;

    #[ORM\Column(nullable: true)]
    private ?int $magentaPourcent = null;

    #[ORM\Column(nullable: true)]
    private ?int $jaunePourcent = null;

    #[ORM\Column(nullable: true)]
    private ?int $bacRecuperation = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateEpuisementNoir = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateEpuisementCyan = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateEpuisementMagenta = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateEpuisementJaune = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateReceptionRapport = null;

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

    public function getDateCapture(): \DateTimeImmutable
    {
        return $this->dateCapture;
    }

    public function setDateCapture(\DateTimeImmutable $dateCapture): self
    {
        $this->dateCapture = $dateCapture;
        return $this;
    }

    public function getNoirPourcent(): ?int
    {
        return $this->noirPourcent;
    }

    public function setNoirPourcent(?int $noirPourcent): self
    {
        $this->noirPourcent = $noirPourcent;
        return $this;
    }

    public function getCyanPourcent(): ?int
    {
        return $this->cyanPourcent;
    }

    public function setCyanPourcent(?int $cyanPourcent): self
    {
        $this->cyanPourcent = $cyanPourcent;
        return $this;
    }

    public function getMagentaPourcent(): ?int
    {
        return $this->magentaPourcent;
    }

    public function setMagentaPourcent(?int $magentaPourcent): self
    {
        $this->magentaPourcent = $magentaPourcent;
        return $this;
    }

    public function getJaunePourcent(): ?int
    {
        return $this->jaunePourcent;
    }

    public function setJaunePourcent(?int $jaunePourcent): self
    {
        $this->jaunePourcent = $jaunePourcent;
        return $this;
    }

    public function getBacRecuperation(): ?int
    {
        return $this->bacRecuperation;
    }

    public function setBacRecuperation(?int $bacRecuperation): self
    {
        $this->bacRecuperation = $bacRecuperation;
        return $this;
    }

    public function getDateEpuisementNoir(): ?\DateTimeImmutable
    {
        return $this->dateEpuisementNoir;
    }

    public function setDateEpuisementNoir(?\DateTimeImmutable $dateEpuisementNoir): self
    {
        $this->dateEpuisementNoir = $dateEpuisementNoir;
        return $this;
    }

    public function getDateEpuisementCyan(): ?\DateTimeImmutable
    {
        return $this->dateEpuisementCyan;
    }

    public function setDateEpuisementCyan(?\DateTimeImmutable $dateEpuisementCyan): self
    {
        $this->dateEpuisementCyan = $dateEpuisementCyan;
        return $this;
    }

    public function getDateEpuisementMagenta(): ?\DateTimeImmutable
    {
        return $this->dateEpuisementMagenta;
    }

    public function setDateEpuisementMagenta(?\DateTimeImmutable $dateEpuisementMagenta): self
    {
        $this->dateEpuisementMagenta = $dateEpuisementMagenta;
        return $this;
    }

    public function getDateEpuisementJaune(): ?\DateTimeImmutable
    {
        return $this->dateEpuisementJaune;
    }

    public function setDateEpuisementJaune(?\DateTimeImmutable $dateEpuisementJaune): self
    {
        $this->dateEpuisementJaune = $dateEpuisementJaune;
        return $this;
    }

    public function getDateReceptionRapport(): ?\DateTimeImmutable
    {
        return $this->dateReceptionRapport;
    }

    public function setDateReceptionRapport(?\DateTimeImmutable $dateReceptionRapport): self
    {
        $this->dateReceptionRapport = $dateReceptionRapport;
        return $this;
    }
}
