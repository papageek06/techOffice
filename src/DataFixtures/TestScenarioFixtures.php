<?php

namespace App\DataFixtures;

use App\Entity\AffectationMateriel;
use App\Entity\Client;
use App\Entity\Contrat;
use App\Entity\ContratLigne;
use App\Entity\FacturationCompteur;
use App\Entity\FacturationPeriode;
use App\Entity\Fabricant;
use App\Entity\Imprimante;
use App\Entity\Modele;
use App\Entity\ReleveCompteur;
use App\Entity\Site;
use App\Enum\Periodicite;
use App\Enum\SourceCompteur;
use App\Enum\StatutContrat;
use App\Enum\StatutFacturation;
use App\Enum\TypeAffectation;
use App\Enum\TypeContrat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture de test : 1 site, 2 imprimantes, relevés sur 6 mois, 1 contrat trimestriel
 */
class TestScenarioFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['test-scenario'];
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Créer 1 client
        $client = new Client();
        $client->setNom('Client Test');
        $client->setActif(true);
        $manager->persist($client);
        $manager->flush();

        // 2. Créer 1 site pour ce client
        $site = new Site();
        $site->setClient($client);
        $site->setNomSite('Site Test');
        $site->setActif(true);
        $manager->persist($site);
        $manager->flush();

        // 3. Créer un fabricant et un modèle pour les imprimantes
        $fabricant = $manager->getRepository(Fabricant::class)->findOneBy(['nomFabricant' => 'HP']);
        if (!$fabricant) {
            $fabricant = new Fabricant();
            $fabricant->setNomFabricant('HP');
            $manager->persist($fabricant);
            $manager->flush();
        }

        $modele = $manager->getRepository(Modele::class)->findOneBy(['referenceModele' => 'LaserJet Pro']);
        if (!$modele) {
            $modele = new Modele();
            $modele->setFabricant($fabricant);
            $modele->setReferenceModele('LaserJet Pro');
            $modele->setTypeImprimante('multifonction');
            $manager->persist($modele);
            $manager->flush();
        }

        // 4. Créer 2 imprimantes pour ce site
        $imprimantes = [];
        for ($i = 1; $i <= 2; $i++) {
            $imprimante = new Imprimante();
            $imprimante->setSite($site);
            $imprimante->setModele($modele);
            $imprimante->setNumeroSerie('TEST-SN-' . str_pad((string)$i, 6, '0', STR_PAD_LEFT));
            $imprimante->setAdresseIp('192.168.1.' . (100 + $i));
            $imprimante->setDateInstallation(new \DateTimeImmutable('-12 months'));
            $imprimante->setStatut(StatutImprimante::ACTIF);
            $manager->persist($imprimante);
            $imprimantes[] = $imprimante;
        }
        $manager->flush();

        // 5. Créer des relevés de compteur pour chaque imprimante sur les 6 derniers mois
        // Environ 1 relevé par mois pour chaque imprimante
        $dateAujourdhui = new \DateTimeImmutable();
        $dateDebut = $dateAujourdhui->modify('-6 months')->modify('first day of this month');
        
        foreach ($imprimantes as $imprimanteIndex => $imprimante) {
            // Compteurs initiaux différents pour chaque imprimante
            $compteurNoirInitial = 10000 + ($imprimanteIndex * 5000);
            $compteurCouleurInitial = 5000 + ($imprimanteIndex * 2000);
            $compteurFaxInitial = 100 + ($imprimanteIndex * 50);
            
            $compteurNoir = $compteurNoirInitial;
            $compteurCouleur = $compteurCouleurInitial;
            $compteurFax = $compteurFaxInitial;
            
            // Créer environ 6 relevés (1 par mois)
            $dateCourante = clone $dateDebut;
            for ($mois = 0; $mois < 6; $mois++) {
                // Date du relevé : environ le 15 de chaque mois
                $dateReleve = $dateCourante->modify("+$mois months")->modify('+14 days')->setTime(8, 0, 0);
                
                // Consommation mensuelle réaliste
                $pagesNoirMois = rand(2000, 4000); // 2000-4000 pages/mois
                $pagesCouleurMois = rand(500, 1500); // 500-1500 pages/mois
                $pagesFaxMois = rand(50, 200); // 50-200 pages/mois
                
                $compteurNoir += $pagesNoirMois;
                $compteurCouleur += $pagesCouleurMois;
                $compteurFax += $pagesFaxMois;
                
                // Vérifier si un relevé existe déjà pour cette date
                $releveExistant = $manager->getRepository(ReleveCompteur::class)
                    ->findOneBy([
                        'imprimante' => $imprimante,
                        'dateReleve' => $dateReleve
                    ]);
                
                if (!$releveExistant) {
                    $releve = new ReleveCompteur();
                    $releve->setImprimante($imprimante);
                    $releve->setDateReleve($dateReleve);
                    $releve->setCompteurNoir($compteurNoir);
                    $releve->setCompteurCouleur($compteurCouleur);
                    $releve->setCompteurFax($compteurFax);
                    $releve->setSource('scan');
                    $releve->setDateReceptionRapport($dateReleve->modify('+1 day'));
                    
                    $manager->persist($releve);
                }
            }
        }
        $manager->flush();

        // 6. Créer 1 contrat
        $dateDebutContrat = $dateDebut->modify('-3 months'); // Contrat commencé il y a 3 mois
        $contrat = new Contrat();
        $contrat->setClient($client);
        $contrat->setReference('TEST-CONT-001');
        $contrat->setTypeContrat(TypeContrat::MAINTENANCE);
        $contrat->setDateDebut($dateDebutContrat);
        $contrat->setDateFin(null); // Contrat actif
        $contrat->setStatut(StatutContrat::ACTIF);
        $contrat->setNotes('Contrat de test pour scénario spécifique');
        $manager->persist($contrat);
        $manager->flush();

        // 7. Créer 1 ligne de contrat trimestrielle
        // La prochaine facturation sera dans 3 mois (trimestriel)
        $prochaineFacturation = $dateAujourdhui->modify('+3 months')->modify('first day of this month');
        
        $contratLigne = new ContratLigne();
        $contratLigne->setContrat($contrat);
        $contratLigne->setSite($site);
        $contratLigne->setLibelle('Ligne Test - Trimestrielle');
        $contratLigne->setPeriodicite(Periodicite::TRIMESTRIEL);
        $contratLigne->setProchaineFacturation($prochaineFacturation);
        $contratLigne->setPrixFixe('150.00'); // 150€ fixe
        $contratLigne->setPrixPageNoir('0.015'); // 0.015€ par page noir
        $contratLigne->setPrixPageCouleur('0.045'); // 0.045€ par page couleur
        $contratLigne->setPagesInclusesNoir(1000);
        $contratLigne->setPagesInclusesCouleur(500);
        $contratLigne->setActif(true);
        $manager->persist($contratLigne);
        $manager->flush();

        // 8. Affecter les 2 imprimantes à cette ligne de contrat
        $affectations = [];
        foreach ($imprimantes as $index => $imprimante) {
            $affectation = new AffectationMateriel();
            $affectation->setContratLigne($contratLigne);
            $affectation->setImprimante($imprimante);
            $affectation->setDateDebut($dateDebutContrat->setTime(0, 0));
            $affectation->setDateFin(null); // Affectation active
            $affectation->setTypeAffectation($index === 0 ? TypeAffectation::PRINCIPALE : TypeAffectation::PRINCIPALE);
            $affectation->setReason(null);
            
            $manager->persist($affectation);
            $affectations[] = $affectation;
        }
        $manager->flush();

        // 9. Créer 1 période de facturation trimestrielle qui correspond aux relevés
        // La période couvre les 3 derniers mois (trimestriel)
        $dateDebutPeriode = $dateAujourdhui->modify('-3 months')->modify('first day of this month');
        $dateFinPeriode = $dateAujourdhui->modify('-1 day'); // Jusqu'à hier
        
        $periode = new FacturationPeriode();
        $periode->setContratLigne($contratLigne);
        $periode->setDateDebut($dateDebutPeriode);
        $periode->setDateFin($dateFinPeriode);
        $periode->setStatut(StatutFacturation::BROUILLON);
        $manager->persist($periode);
        $manager->flush();

        // 10. Créer les compteurs de facturation pour cette période
        foreach ($affectations as $affectation) {
            $imprimante = $affectation->getImprimante();
            
            // Trouver le relevé de début (le plus proche avant ou à la date de début)
            $releveDebut = $manager->getRepository(ReleveCompteur::class)
                ->createQueryBuilder('r')
                ->where('r.imprimante = :imprimante')
                ->andWhere('r.dateReleve <= :dateDebut')
                ->setParameter('imprimante', $imprimante)
                ->setParameter('dateDebut', $dateDebutPeriode)
                ->orderBy('r.dateReleve', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            // Trouver le relevé de fin (le plus proche avant ou à la date de fin)
            $releveFin = $manager->getRepository(ReleveCompteur::class)
                ->createQueryBuilder('r')
                ->where('r.imprimante = :imprimante')
                ->andWhere('r.dateReleve <= :dateFin')
                ->setParameter('imprimante', $imprimante)
                ->setParameter('dateFin', $dateFinPeriode)
                ->orderBy('r.dateReleve', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            if (!$releveDebut) {
                continue; // Pas de relevé de début, on skip
            }
            
            $compteurDebutNoir = $releveDebut->getCompteurNoir() ?? 0;
            $compteurDebutCouleur = $releveDebut->getCompteurCouleur();
            
            if ($releveFin) {
                $compteurFinNoir = $releveFin->getCompteurNoir() ?? 0;
                $compteurFinCouleur = $releveFin->getCompteurCouleur();
                $compteurFinEstime = false;
                $dateReleveFin = $releveFin->getDateReleve();
            } else {
                // Pas de relevé de fin, utiliser le compteur de début (pas de consommation)
                $compteurFinNoir = $compteurDebutNoir;
                $compteurFinCouleur = $compteurDebutCouleur;
                $compteurFinEstime = true;
                $dateReleveFin = null;
            }
            
            $facturationCompteur = new FacturationCompteur();
            $facturationCompteur->setFacturationPeriode($periode);
            $facturationCompteur->setAffectationMateriel($affectation);
            $facturationCompteur->setCompteurDebutNoir($compteurDebutNoir);
            $facturationCompteur->setCompteurFinNoir($compteurFinNoir);
            $facturationCompteur->setCompteurDebutCouleur($compteurDebutCouleur);
            $facturationCompteur->setCompteurFinCouleur($compteurFinCouleur);
            
            // Source
            $sourceDebut = SourceCompteur::SCAN;
            if ($releveDebut) {
                $sourceStr = $releveDebut->getSource();
                $sourceDebut = match (strtolower($sourceStr)) {
                    'snmp' => SourceCompteur::SNMP,
                    'scan', 'csv' => SourceCompteur::SCAN,
                    default => SourceCompteur::MANUEL,
                };
            }
            
            $sourceFin = SourceCompteur::SCAN;
            $relevePourSource = $releveFin ?? $releveDebut;
            if ($relevePourSource) {
                $sourceStr = $relevePourSource->getSource();
                $sourceFin = match (strtolower($sourceStr)) {
                    'snmp' => SourceCompteur::SNMP,
                    'scan', 'csv' => SourceCompteur::SCAN,
                    default => SourceCompteur::MANUEL,
                };
            }
            
            $facturationCompteur->setSourceDebut($sourceDebut);
            $facturationCompteur->setSourceFin($sourceFin);
            $facturationCompteur->setCompteurFinEstime($compteurFinEstime);
            $facturationCompteur->setDateReleveFin($dateReleveFin);
            
            $manager->persist($facturationCompteur);
        }
        
        $manager->flush();
    }
}
