<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TypeDefautAlert;
use App\Repository\DefautImprimanteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Défaut enregistré à partir d'une Smart Alert (site, imprimante, type) pour générer des alertes stock.
 */
#[ORM\Entity(repositoryClass: DefautImprimanteRepository::class)]
#[ORM\Table(name: 'defaut_imprimante')]
#[ORM\Index(columns: ['site_nom'], name: 'idx_defaut_site_nom')]
#[ORM\Index(columns: ['type_defaut'], name: 'idx_defaut_type')]
#[ORM\Index(columns: ['received_at'], name: 'idx_defaut_received_at')]
class DefautImprimante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, name: 'site_nom')]
    private string $siteNom;

    #[ORM\ManyToOne(targetEntity: Imprimante::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Imprimante $imprimante = null;

    #[ORM\Column(enumType: TypeDefautAlert::class, name: 'type_defaut')]
    private TypeDefautAlert $typeDefaut;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    /** Couleur toner concernée (noir, cyan, magenta, jaune) pour déduction stock si changement_cartouche */
    #[ORM\Column(length: 20, nullable: true, name: 'couleur_toner')]
    private ?string $couleurToner = null;

    #[ORM\ManyToOne(targetEntity: InboundAlert::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL', name: 'inbound_alert_id')]
    private ?InboundAlert $inboundAlert = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, name: 'received_at')]
    private ?\DateTimeImmutable $receivedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    /** Série / IP / nom machine extrait du body (si imprimante non résolue) */
    #[ORM\Column(length: 120, nullable: true, name: 'machine_serial')]
    private ?string $machineSerial = null;

    #[ORM\Column(length: 45, nullable: true, name: 'machine_ip')]
    private ?string $machineIp = null;

    #[ORM\Column(length: 120, nullable: true, name: 'machine_nom')]
    private ?string $machineNom = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiteNom(): string
    {
        return $this->siteNom;
    }

    public function setSiteNom(string $siteNom): static
    {
        $this->siteNom = $siteNom;
        return $this;
    }

    public function getImprimante(): ?Imprimante
    {
        return $this->imprimante;
    }

    public function setImprimante(?Imprimante $imprimante): static
    {
        $this->imprimante = $imprimante;
        return $this;
    }

    public function getTypeDefaut(): TypeDefautAlert
    {
        return $this->typeDefaut;
    }

    public function setTypeDefaut(TypeDefautAlert $typeDefaut): static
    {
        $this->typeDefaut = $typeDefaut;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getCouleurToner(): ?string
    {
        return $this->couleurToner;
    }

    public function setCouleurToner(?string $couleurToner): static
    {
        $this->couleurToner = $couleurToner;
        return $this;
    }

    public function getInboundAlert(): ?InboundAlert
    {
        return $this->inboundAlert;
    }

    public function setInboundAlert(?InboundAlert $inboundAlert): static
    {
        $this->inboundAlert = $inboundAlert;
        return $this;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeImmutable $receivedAt): static
    {
        $this->receivedAt = $receivedAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getMachineSerial(): ?string
    {
        return $this->machineSerial;
    }

    public function setMachineSerial(?string $machineSerial): static
    {
        $this->machineSerial = $machineSerial;
        return $this;
    }

    public function getMachineIp(): ?string
    {
        return $this->machineIp;
    }

    public function setMachineIp(?string $machineIp): static
    {
        $this->machineIp = $machineIp;
        return $this;
    }

    public function getMachineNom(): ?string
    {
        return $this->machineNom;
    }

    public function setMachineNom(?string $machineNom): static
    {
        $this->machineNom = $machineNom;
        return $this;
    }
}
