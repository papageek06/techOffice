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
            // Calculer la date de début : reculer de la période depuis la prochaine facturation
            // et aller au 1er jour du mois
            $dateDebut = $this->calculateDateDebut($prochaineFacturation, $ligne->getPeriodicite());
            // La date de fin est la veille de la prochaine facturation
            $dateFin = $prochaineFacturation->modify('-1 day');

            if ($this->facturationPeriodeRepository->existsForPeriod($ligne, $dateDebut, $dateFin)) {
                // Période déjà existante, passer à la suivante
                $prochaineFacturation = $this->calculateProchaineFacturation($prochaineFacturation, $ligne->getPeriodicite());
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

            // Mettre à jour la prochaine facturation : avancer de la période
            $prochaineFacturation = $this->calculateProchaineFacturation($prochaineFacturation, $ligne->getPeriodicite());
        }

        // Mettre à jour la prochaine facturation de la ligne si des périodes ont été créées
        if (!empty($periodesCreees)) {
            $ligne->setProchaineFacturation($prochaineFacturation);
        }

        return $periodesCreees;
    }

    /**
     * Calcule la date de début d'une période en reculant de la période depuis la prochaine facturation
     * et en allant au 1er jour du mois
     * 
     * Exemple : si prochaineFacturation = 01/02/2026 et périodicité = TRIMESTRIEL
     * Alors dateDebut = 01/11/2025 (3 mois avant, 1er jour du mois)
     */
    private function calculateDateDebut(\DateTimeImmutable $prochaineFacturation, Periodicite $periodicite): \DateTimeImmutable
    {
        // Reculer de la période
        $dateDebut = match ($periodicite) {
            Periodicite::MENSUEL => $prochaineFacturation->modify('-1 month'),
            Periodicite::TRIMESTRIEL => $prochaineFacturation->modify('-3 months'),
            Periodicite::SEMESTRIEL => $prochaineFacturation->modify('-6 months'),
            Periodicite::ANNUEL => $prochaineFacturation->modify('-1 year'),
        };
        
        // Aller au 1er jour du mois
        return $dateDebut->modify('first day of this month');
    }

    /**
     * Calcule la prochaine date de facturation en avançant de la période
     * 
     * Exemple : si prochaineFacturation = 01/02/2026 et périodicité = TRIMESTRIEL
     * Alors nouvelle prochaineFacturation = 01/05/2026 (3 mois après)
     */
    private function calculateProchaineFacturation(\DateTimeImmutable $prochaineFacturation, Periodicite $periodicite): \DateTimeImmutable
    {
        return match ($periodicite) {
            Periodicite::MENSUEL => $prochaineFacturation->modify('+1 month'),
            Periodicite::TRIMESTRIEL => $prochaineFacturation->modify('+3 months'),
            Periodicite::SEMESTRIEL => $prochaineFacturation->modify('+6 months'),
            Periodicite::ANNUEL => $prochaineFacturation->modify('+1 year'),
        };
    }
}
