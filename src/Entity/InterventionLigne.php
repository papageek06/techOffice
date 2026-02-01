<?php

namespace App\Entity;

use App\Repository\InterventionLigneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ligne d'une intervention livraison toner : pièce et quantité livrée.
 * Utilisée pour appliquer les mouvements de stock à la clôture.
 */
#[ORM\Entity(repositoryClass: InterventionLigneRepository::class)]
#[ORM\Table(name: 'intervention_ligne')]
class InterventionLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Intervention $intervention;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Piece $piece;

    #[ORM\Column]
    private int $quantite = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntervention(): Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(Intervention $intervention): static
    {
        $this->intervention = $intervention;
        return $this;
    }

    public function getPiece(): Piece
    {
        return $this->piece;
    }

    public function setPiece(Piece $piece): static
    {
        $this->piece = $piece;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }
}
