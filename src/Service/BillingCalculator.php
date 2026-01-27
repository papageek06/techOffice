<?php

namespace App\Service;

use App\Entity\ContratLigne;
use App\Entity\FacturationCompteur;
use App\Entity\FacturationPeriode;

/**
 * Service pour calculer les montants de facturation
 */
class BillingCalculator
{
    /**
     * Calcule le montant total pour une période de facturation
     * 
     * @param FacturationPeriode $periode
     * @return array ['montant' => float, 'pagesNoir' => int, 'pagesCouleur' => int, 'details' => array]
     */
    public function calculateForPeriod(FacturationPeriode $periode): array
    {
        $contratLigne = $periode->getContratLigne();
        $montantTotal = 0.0;
        $pagesNoirTotal = 0;
        $pagesCouleurTotal = 0;
        $details = [];

        // Prix fixe
        $prixFixe = $contratLigne->getPrixFixe();
        if ($prixFixe !== null) {
            $montantTotal += (float) $prixFixe;
            $details[] = [
                'type' => 'prix_fixe',
                'libelle' => 'Prix fixe',
                'quantite' => 1,
                'prix_unitaire' => (float) $prixFixe,
                'montant' => (float) $prixFixe,
            ];
        }

        // Calculer pour chaque compteur de facturation
        foreach ($periode->getFacturationCompteurs() as $compteur) {
            $pagesNoir = $compteur->getPagesNoir();
            $pagesCouleur = $compteur->getPagesCouleur();

            $pagesNoirTotal += $pagesNoir;
            $pagesCouleurTotal += $pagesCouleur;

            // Pages noires
            if ($pagesNoir > 0 && $contratLigne->getPrixPageNoir() !== null) {
                $pagesInclusesNoir = $contratLigne->getPagesInclusesNoir() ?? 0;
                $pagesFacturablesNoir = max(0, $pagesNoir - $pagesInclusesNoir);

                if ($pagesFacturablesNoir > 0) {
                    $prixPageNoir = (float) $contratLigne->getPrixPageNoir();
                    $montantNoir = $pagesFacturablesNoir * $prixPageNoir;
                    $montantTotal += $montantNoir;

                    $details[] = [
                        'type' => 'pages_noir',
                        'libelle' => sprintf('Pages noir - %s', $compteur->getAffectationMateriel()->getImprimante()->getNumeroSerie()),
                        'quantite' => $pagesFacturablesNoir,
                        'pages_incluses' => $pagesInclusesNoir,
                        'prix_unitaire' => $prixPageNoir,
                        'montant' => $montantNoir,
                    ];
                }
            }

            // Pages couleur
            if ($pagesCouleur > 0 && $contratLigne->getPrixPageCouleur() !== null) {
                $pagesInclusesCouleur = $contratLigne->getPagesInclusesCouleur() ?? 0;
                $pagesFacturablesCouleur = max(0, $pagesCouleur - $pagesInclusesCouleur);

                if ($pagesFacturablesCouleur > 0) {
                    $prixPageCouleur = (float) $contratLigne->getPrixPageCouleur();
                    $montantCouleur = $pagesFacturablesCouleur * $prixPageCouleur;
                    $montantTotal += $montantCouleur;

                    $details[] = [
                        'type' => 'pages_couleur',
                        'libelle' => sprintf('Pages couleur - %s', $compteur->getAffectationMateriel()->getImprimante()->getNumeroSerie()),
                        'quantite' => $pagesFacturablesCouleur,
                        'pages_incluses' => $pagesInclusesCouleur,
                        'prix_unitaire' => $prixPageCouleur,
                        'montant' => $montantCouleur,
                    ];
                }
            }
        }

        return [
            'montant' => round($montantTotal, 2),
            'pagesNoir' => $pagesNoirTotal,
            'pagesCouleur' => $pagesCouleurTotal,
            'details' => $details,
        ];
    }

    /**
     * Calcule le montant pour une ligne de contrat sur une période donnée
     * (méthode utilitaire pour prévisualisation)
     */
    public function calculatePreview(ContratLigne $contratLigne, \DateTimeImmutable $dateDebut, \DateTimeImmutable $dateFin): array
    {
        // Créer une période temporaire pour le calcul
        $periode = new FacturationPeriode();
        $periode->setContratLigne($contratLigne);
        $periode->setDateDebut($dateDebut);
        $periode->setDateFin($dateFin);

        // Note: Cette méthode ne résout pas les compteurs, elle est juste pour prévisualisation
        // En production, il faudrait utiliser CounterSnapshotResolver d'abord

        return [
            'montant' => (float) ($contratLigne->getPrixFixe() ?? 0),
            'pagesNoir' => 0,
            'pagesCouleur' => 0,
            'details' => [],
        ];
    }
}
