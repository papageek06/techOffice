<?php

namespace App\Service;

use App\Entity\AffectationMateriel;
use App\Entity\ContratLigne;
use App\Entity\Imprimante;
use App\Enum\TypeAffectation;
use App\Repository\AffectationMaterielRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer les affectations de matériel
 * Gère automatiquement la clôture de l'affectation précédente lors d'un changement
 */
class AffectationMaterielManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AffectationMaterielRepository $affectationMaterielRepository
    ) {
    }

    /**
     * Crée une nouvelle affectation et clôture l'ancienne si nécessaire
     * 
     * @param ContratLigne $contratLigne
     * @param Imprimante $imprimante
     * @param \DateTimeImmutable $dateDebut
     * @param TypeAffectation $typeAffectation
     * @param string|null $reason Raison du changement
     * @return AffectationMateriel La nouvelle affectation créée
     */
    public function createAffectation(
        ContratLigne $contratLigne,
        Imprimante $imprimante,
        \DateTimeImmutable $dateDebut,
        TypeAffectation $typeAffectation = TypeAffectation::PRINCIPALE,
        ?string $reason = null
    ): AffectationMateriel {
        // Clôturer l'affectation active existante
        $affectationActive = $this->affectationMaterielRepository->findActiveForContratLigne($contratLigne);
        if ($affectationActive !== null) {
            $dateFinAncienne = $dateDebut->modify('-1 day');
            $affectationActive->setDateFin($dateFinAncienne);
        }

        // Créer la nouvelle affectation
        $nouvelleAffectation = new AffectationMateriel();
        $nouvelleAffectation->setContratLigne($contratLigne);
        $nouvelleAffectation->setImprimante($imprimante);
        $nouvelleAffectation->setDateDebut($dateDebut);
        $nouvelleAffectation->setTypeAffectation($typeAffectation);
        $nouvelleAffectation->setReason($reason);

        $this->entityManager->persist($nouvelleAffectation);
        $this->entityManager->flush();

        return $nouvelleAffectation;
    }

    /**
     * Clôture une affectation active
     */
    public function closeAffectation(AffectationMateriel $affectation, \DateTimeImmutable $dateFin, ?string $reason = null): void
    {
        if ($affectation->getDateFin() !== null) {
            throw new \RuntimeException('Cette affectation est déjà clôturée');
        }

        $affectation->setDateFin($dateFin);
        if ($reason !== null) {
            $affectation->setReason($reason);
        }

        $this->entityManager->flush();
    }
}
