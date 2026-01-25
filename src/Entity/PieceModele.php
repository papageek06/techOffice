<?php

namespace App\Entity;

use App\Enum\PieceRoleModele;
use App\Repository\PieceModeleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Table de compatibilité pièce ↔ modèle (pivot avec rôle)
 * Unique constraint sur (piece_id, modele_id, role) pour éviter les doublons
 * Note: Doctrine ne supporte pas bien les PK composites avec enums, on utilise une contrainte unique
 */
#[ORM\Entity(repositoryClass: PieceModeleRepository::class)]
#[ORM\Table(name: 'piece_modele')]
#[ORM\UniqueConstraint(name: 'uniq_piece_modele_role', columns: ['piece_id', 'modele_id', 'role'])]
#[ORM\Index(name: 'idx_modele_id', columns: ['modele_id'])]
class PieceModele
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pieceModeles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Piece $piece;

    #[ORM\ManyToOne(inversedBy: 'pieceModeles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Modele $modele;

    #[ORM\Column(enumType: PieceRoleModele::class)]
    private PieceRoleModele $role;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPiece(): Piece
    {
        return $this->piece;
    }

    public function setPiece(Piece $piece): self
    {
        $this->piece = $piece;
        return $this;
    }

    public function getModele(): Modele
    {
        return $this->modele;
    }

    public function setModele(Modele $modele): self
    {
        $this->modele = $modele;
        return $this;
    }

    public function getRole(): PieceRoleModele
    {
        return $this->role;
    }

    public function setRole(PieceRoleModele $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }
}
