<?php

namespace App\Service;

use App\Entity\ContratLigne;
use App\Entity\FacturationPeriode;
use App\Enum\Periodicite;
use App\Enum\StatutFacturation;
use App\Repository\ContratLigneRepository;
use App\Repository\FacturationPeriodeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour générer les périodes de facturation selon la périodicité
 */
class BillingPeriodGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContratLigneRepository $contratLigneRepository,
        private readonly FacturationPeriodeRepository $facturationPeriodeRepository
    ) {
    }

    /**
     * Génère les périodes de facturation pour toutes les lignes de contrat éligibles
     * 
     * @param \DateTimeImmutable|null $dateReference Date de référence (par défaut aujourd'hui)
     * @return array Liste des périodes créées
     */
    public function generatePeriods(?\DateTimeImmutable $dateReference = null): array
    {
        if ($dateReference === null) {
            $dateReference = new \DateTimeImmutable();
        }

        $lignes = $this->contratLigneRepository->findAvecFacturationDue($dateReference);
        $periodesCreees = [];

        foreach ($lignes as $ligne) {
            $periodes = $this->generatePeriodsForLigne($ligne, $dateReference);
            $periodesCreees = array_merge($periodesCreees, $periodes);
        }

        if (!empty($periodesCreees)) {
            $this->entityManager->flush();
        }

        return $periodesCreees;
    }

    /**
     * Génère les périodes pour une ligne de contrat spécifique
     * 
     * @param ContratLigne $ligne
     * @param \DateTimeImmutable $dateReference
     * @return array Liste des périodes créées
     */
    public function generatePeriodsForLigne(ContratLigne $ligne, \DateTimeImmutable $dateReference): array
    {
        $periodesCreees = [];
        $prochaineFacturation = $ligne->getProchaineFacturation();

        // Générer jusqu'à 4 périodes à l'avance ou jusqu'à la date de référence + 1 an
        $dateLimite = $dateReference->modify('+1 year');
        $maxPeriodes = 4;

        for ($i = 0; $i < $maxPeriodes && $prochaineFacturation <= $dateLimite; $i++) {
            // Vérifier si la période existe déjà
            $dateDebut = clone $prochaineFacturation;
            $dateFin = $this->calculateDateFin($dateDebut, $ligne->getPeriodicite());

            if ($this->facturationPeriodeRepository->existsForPeriod($ligne, $dateDebut, $dateFin)) {
                // Période déjà existante, passer à la suivante
                $prochaineFacturation = $this->calculateProchaineFacturation($dateFin, $ligne->getPeriodicite());
                continue;
            }

            // Créer la nouvelle période
            $periode = new FacturationPeriode();
            $periode->setContratLigne($ligne);
            $periode->setDateDebut($dateDebut);
            $periode->setDateFin($dateFin);
            $periode->setStatut(StatutFacturation::BROUILLON);

            $this->entityManager->persist($periode);
            $periodesCreees[] = $periode;

            // Mettre à jour la prochaine facturation de la ligne
            $prochaineFacturation = $this->calculateProchaineFacturation($dateFin, $ligne->getPeriodicite());
        }

        // Mettre à jour la prochaine facturation de la ligne si des périodes ont été créées
        if (!empty($periodesCreees)) {
            $ligne->setProchaineFacturation($prochaineFacturation);
        }

        return $periodesCreees;
    }

    /**
     * Calcule la date de fin d'une période selon la périodicité
     */
    private function calculateDateFin(\DateTimeImmutable $dateDebut, Periodicite $periodicite): \DateTimeImmutable
    {
        return match ($periodicite) {
            Periodicite::MENSUEL => $dateDebut->modify('+1 month -1 day'),
            Periodicite::TRIMESTRIEL => $dateDebut->modify('+3 months -1 day'),
            Periodicite::SEMESTRIEL => $dateDebut->modify('+6 months -1 day'),
            Periodicite::ANNUEL => $dateDebut->modify('+1 year -1 day'),
        };
    }

    /**
     * Calcule la prochaine date de facturation (début de la période suivante)
     */
    private function calculateProchaineFacturation(\DateTimeImmutable $dateFin, Periodicite $periodicite): \DateTimeImmutable
    {
        return $dateFin->modify('+1 day');
    }
}
