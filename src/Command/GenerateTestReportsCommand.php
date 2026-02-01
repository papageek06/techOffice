<?php

namespace App\Command;

use App\Entity\EtatConsommable;
use App\Entity\Imprimante;
use App\Entity\ReleveCompteur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate:test-reports',
    description: 'Génère des rapports de test quotidiens pour une imprimante (6 derniers mois)',
)]
class GenerateTestReportsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('imprimante_id', InputArgument::REQUIRED, 'ID de l\'imprimante')
            ->addOption('months', null, InputOption::VALUE_OPTIONAL, 'Nombre de mois à générer', 6)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Supprimer les rapports existants avant de générer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $imprimanteId = (int) $input->getArgument('imprimante_id');
        $nbMois = (int) $input->getOption('months');
        $force = $input->getOption('force');

        $io->title('Génération de rapports de test');

        // Récupérer l'imprimante
        $imprimante = $this->em->getRepository(Imprimante::class)->find($imprimanteId);
        if (!$imprimante) {
            $io->error("Imprimante avec l'ID $imprimanteId introuvable.");
            return Command::FAILURE;
        }

        $io->info(sprintf(
            'Imprimante trouvée: %s (Série: %s, Site: %s)',
            $imprimante->getModele()->getReferenceModele(),
            $imprimante->getNumeroSerie(),
            $imprimante->getSite()->getNomSite()
        ));

        // Supprimer les rapports existants si --force
        if ($force) {
            $io->warning('Suppression des rapports existants...');
            
            // Supprimer les états consommables
            $etats = $this->em->getRepository(EtatConsommable::class)
                ->findBy(['imprimante' => $imprimante]);
            foreach ($etats as $etat) {
                $this->em->remove($etat);
            }

            // Supprimer les relevés de compteur
            $releves = $this->em->getRepository(ReleveCompteur::class)
                ->findBy(['imprimante' => $imprimante]);
            foreach ($releves as $releve) {
                $this->em->remove($releve);
            }

            $this->em->flush();
            $io->success('Rapports existants supprimés');
        }

        // Vérifier s'il existe déjà des relevés
        $relevesExistants = $this->em->getRepository(ReleveCompteur::class)
            ->findBy(['imprimante' => $imprimante], ['dateReleve' => 'DESC'], 1);
        
        $dateDebut = (new \DateTimeImmutable())->modify("-$nbMois months")->modify('first day of this month');
        $dateFin = new \DateTimeImmutable();

        // Valeurs de départ pour les compteurs
        $compteurNoirDepart = 50000;
        $compteurCouleurDepart = 30000;
        $compteurFaxDepart = 1000;

        // Si des relevés existent, partir des dernières valeurs
        if (!empty($relevesExistants) && !$force) {
            $dernierReleve = $relevesExistants[0];
            $compteurNoirDepart = $dernierReleve->getCompteurNoir() ?? $compteurNoirDepart;
            $compteurCouleurDepart = $dernierReleve->getCompteurCouleur() ?? $compteurCouleurDepart;
            $compteurFaxDepart = $dernierReleve->getCompteurFax() ?? $compteurFaxDepart;
            $dateDebut = $dernierReleve->getDateReleve()->modify('+1 day');
            
            $io->note(sprintf(
                'Reprise depuis le dernier relevé: %s (N: %d, C: %d)',
                $dernierReleve->getDateReleve()->format('d/m/Y'),
                $compteurNoirDepart,
                $compteurCouleurDepart
            ));
        }

        // Valeurs de départ pour les niveaux d'encre (en pourcentage)
        $noirPourcent = 90;
        $cyanPourcent = 85;
        $magentaPourcent = 80;
        $jaunePourcent = 75;
        $bacPourcent = 20;

        // Générer les rapports jour par jour
        $dateCourante = clone $dateDebut;
        $compteurNoir = $compteurNoirDepart;
        $compteurCouleur = $compteurCouleurDepart;
        $compteurFax = $compteurFaxDepart;

        $nbRapports = 0;
        $progressBar = $io->createProgressBar();
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %message%');
        $progressBar->setMessage('Génération en cours...');

        $totalJours = $dateCourante->diff($dateFin)->days;
        $progressBar->start($totalJours);

        while ($dateCourante <= $dateFin) {
            // Vérifier si un relevé existe déjà pour cette date
            $releveExistant = $this->em->getRepository(ReleveCompteur::class)
                ->findOneBy([
                    'imprimante' => $imprimante,
                    'dateReleve' => $dateCourante->setTime(8, 0, 0) // 8h du matin
                ]);

            if (!$releveExistant) {
                // Consommation quotidienne réaliste (variation selon le jour de la semaine)
                $jourSemaine = (int) $dateCourante->format('N'); // 1=lundi, 7=dimanche
                
                // Moins d'impressions le week-end
                $facteurJour = match($jourSemaine) {
                    6, 7 => 0.3, // Samedi/Dimanche: 30% de la consommation normale
                    default => 1.0, // Semaine normale
                };

                // Variation aléatoire de ±20%
                $variation = 1.0 + (rand(-20, 20) / 100);
                
                // Consommation quotidienne moyenne
                $pagesNoirQuotidiennes = (int) (rand(50, 150) * $facteurJour * $variation);
                $pagesCouleurQuotidiennes = (int) (rand(20, 80) * $facteurJour * $variation);
                $pagesFaxQuotidiennes = rand(0, 5) * ($facteurJour > 0.5 ? 1 : 0);

                // Mettre à jour les compteurs
                $compteurNoir += $pagesNoirQuotidiennes;
                $compteurCouleur += $pagesCouleurQuotidiennes;
                $compteurFax += $pagesFaxQuotidiennes;

                // Créer le relevé de compteur
                $releve = new ReleveCompteur();
                $releve->setImprimante($imprimante);
                $releve->setDateReleve($dateCourante->setTime(8, 0, 0));
                $releve->setCompteurNoir($compteurNoir);
                $releve->setCompteurCouleur($compteurCouleur);
                $releve->setCompteurFax($compteurFax);
                $releve->setSource('scan');
                $releve->setDateReceptionRapport($dateCourante->setTime(9, 0, 0));

                $this->em->persist($releve);

                // Calculer la consommation d'encre (approximative)
                // 1% d'encre ≈ 100-200 pages selon le modèle
                $consommationNoir = $pagesNoirQuotidiennes / 150; // ~150 pages par %
                $consommationCouleur = $pagesCouleurQuotidiennes / 100; // ~100 pages par %

                // Diminuer les niveaux d'encre de manière cohérente
                $noirPourcent = max(0, $noirPourcent - $consommationNoir);
                $cyanPourcent = max(0, $cyanPourcent - ($consommationCouleur * 0.8));
                $magentaPourcent = max(0, $magentaPourcent - ($consommationCouleur * 0.9));
                $jaunePourcent = max(0, $jaunePourcent - ($consommationCouleur * 0.85));
                
                // Le bac augmente avec les impressions
                $bacPourcent = min(100, $bacPourcent + ($pagesNoirQuotidiennes + $pagesCouleurQuotidiennes) / 500);

                // Réinitialiser les niveaux si trop bas (changement de cartouche)
                if ($noirPourcent <= 5) {
                    $noirPourcent = 100;
                }
                if ($cyanPourcent <= 5) {
                    $cyanPourcent = 100;
                }
                if ($magentaPourcent <= 5) {
                    $magentaPourcent = 100;
                }
                if ($jaunePourcent <= 5) {
                    $jaunePourcent = 100;
                }
                if ($bacPourcent >= 95) {
                    $bacPourcent = 0; // Vidage du bac
                }

                // Créer l'état consommable
                $etat = new EtatConsommable();
                $etat->setImprimante($imprimante);
                $etat->setDateCapture($dateCourante->setTime(8, 0, 0));
                $etat->setNoirPourcent((int) round($noirPourcent));
                $etat->setCyanPourcent((int) round($cyanPourcent));
                $etat->setMagentaPourcent((int) round($magentaPourcent));
                $etat->setJaunePourcent((int) round($jaunePourcent));
                $etat->setBacRecuperation((int) round($bacPourcent));
                $etat->setDateReceptionRapport($dateCourante->setTime(9, 0, 0));

                // Calculer les dates d'épuisement prévisionnelles (si niveau < 30%)
                if ($noirPourcent < 30) {
                    $joursRestants = (int) ($noirPourcent * 150 / max(1, $pagesNoirQuotidiennes));
                    $etat->setDateEpuisementNoir($dateCourante->modify("+$joursRestants days"));
                }
                if ($cyanPourcent < 30) {
                    $joursRestants = (int) ($cyanPourcent * 100 / max(1, $pagesCouleurQuotidiennes * 0.8));
                    $etat->setDateEpuisementCyan($dateCourante->modify("+$joursRestants days"));
                }
                if ($magentaPourcent < 30) {
                    $joursRestants = (int) ($magentaPourcent * 100 / max(1, $pagesCouleurQuotidiennes * 0.9));
                    $etat->setDateEpuisementMagenta($dateCourante->modify("+$joursRestants days"));
                }
                if ($jaunePourcent < 30) {
                    $joursRestants = (int) ($jaunePourcent * 100 / max(1, $pagesCouleurQuotidiennes * 0.85));
                    $etat->setDateEpuisementJaune($dateCourante->modify("+$joursRestants days"));
                }

                $this->em->persist($etat);
                $nbRapports++;

                // Flush tous les 30 jours pour éviter la surcharge mémoire
                if ($nbRapports % 30 === 0) {
                    $this->em->flush();
                }
            }

            $progressBar->setMessage(sprintf(
                'Jour: %s | N: %d | C: %d',
                $dateCourante->format('d/m/Y'),
                $compteurNoir,
                $compteurCouleur
            ));
            $progressBar->advance();

            // Passer au jour suivant
            $dateCourante = $dateCourante->modify('+1 day');
        }

        $this->em->flush();
        $progressBar->finish();
        $io->newLine(2);

        $io->success([
            sprintf('%d rapports générés avec succès !', $nbRapports),
            sprintf('Période: %s → %s', $dateDebut->format('d/m/Y'), $dateFin->format('d/m/Y')),
            sprintf('Compteurs finaux: Noir: %d | Couleur: %d | Fax: %d', $compteurNoir, $compteurCouleur, $compteurFax),
            sprintf('Niveaux finaux: Noir: %d%% | Cyan: %d%% | Magenta: %d%% | Jaune: %d%% | Bac: %d%%',
                (int) round($noirPourcent),
                (int) round($cyanPourcent),
                (int) round($magentaPourcent),
                (int) round($jaunePourcent),
                (int) round($bacPourcent)
            ),
        ]);

        return Command::SUCCESS;
    }
}
