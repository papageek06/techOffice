<?php

namespace App\Command;

use App\Service\ImportCsvService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:init:sqlite',
    description: 'Initialise SQLite pour les tests/fixtures et importe un CSV',
)]
class InitSqliteCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImportCsvService $importService,
        private ParameterBagInterface $parameterBag,
        private Filesystem $filesystem
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('csv', null, InputOption::VALUE_OPTIONAL, 'Chemin vers le fichier CSV à importer')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Forcer la réinitialisation de la base de données')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Initialisation SQLite - TechOffice');

        // Vérifier que nous sommes en environnement test
        $env = $_ENV['APP_ENV'] ?? 'dev';
        if ($env !== 'test') {
            $io->warning('Cette commande est conçue pour l\'environnement "test".');
            $io->note('Pour utiliser SQLite, exécutez avec: APP_ENV=test php bin/console app:init:sqlite');
        }

        $connection = $this->em->getConnection();
        $dbPath = $this->getDatabasePath($connection);

        // Supprimer la base existante si --force
        if ($input->getOption('force') && $dbPath && file_exists($dbPath)) {
            $io->warning('Suppression de la base de données existante...');
            unlink($dbPath);
            $io->success('Base de données supprimée');
        }

        // Créer le répertoire var si nécessaire
        $varDir = $this->parameterBag->get('kernel.project_dir') . '/var';
        if (!$this->filesystem->exists($varDir)) {
            $this->filesystem->mkdir($varDir);
            $io->info('Répertoire var créé');
        }

        // Vérifier/créer la base SQLite
        if ($connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            $io->info('Base SQLite détectée');
            
            // Créer la base si elle n'existe pas
            if ($dbPath && !file_exists($dbPath)) {
                $this->filesystem->touch($dbPath);
                $io->success('Base SQLite créée : ' . $dbPath);
            } else {
                $io->info('Base SQLite existante : ' . $dbPath);
            }
        } else {
            $io->error('Cette commande nécessite SQLite. Vérifiez votre configuration DATABASE_URL dans .env.test');
            return Command::FAILURE;
        }

        // Exécuter les migrations
        $io->section('Exécution des migrations');
        $io->note('Exécutez manuellement: php bin/console doctrine:migrations:migrate --env=test --no-interaction');
        
        // Vérifier si des tables existent déjà
        try {
            $tables = $connection->createSchemaManager()->listTableNames();
            if (empty($tables)) {
                $io->warning('Aucune table trouvée. Exécutez les migrations d\'abord.');
                $io->note('Commande: php bin/console doctrine:migrations:migrate --env=test --no-interaction');
            } else {
                $io->success(sprintf('%d table(s) trouvée(s)', count($tables)));
            }
        } catch (\Exception $e) {
            $io->error('Erreur lors de la vérification des tables : ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Importer le CSV si fourni
        $csvPath = $input->getOption('csv');
        if ($csvPath) {
            if (!file_exists($csvPath)) {
                $io->error("Le fichier CSV n'existe pas : $csvPath");
                return Command::FAILURE;
            }

            $io->section('Import du CSV');
            $io->info("Import du fichier : $csvPath");

            try {
                $result = $this->importService->import($csvPath);

                if (!empty($result['errors'])) {
                    $io->warning(sprintf('%d erreur(s) rencontrée(s)', count($result['errors'])));
                    foreach (array_slice($result['errors'], 0, 10) as $error) {
                        $io->writeln("  - $error");
                    }
                    if (count($result['errors']) > 10) {
                        $io->note(sprintf('... et %d erreur(s) supplémentaire(s)', count($result['errors']) - 10));
                    }
                }

                $io->success([
                    'Import terminé !',
                    sprintf('  ✓ %d ligne(s) importée(s)', $result['success']),
                    sprintf('  ⊘ %d ligne(s) ignorée(s)', $result['skipped']),
                    sprintf('  ✗ %d erreur(s)', count($result['errors'])),
                ]);
            } catch (\Exception $e) {
                $io->error('Erreur lors de l\'import : ' . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $io->note('Aucun CSV fourni. Utilisez --csv pour importer un fichier.');
        }

        $io->success('Initialisation SQLite terminée !');
        return Command::SUCCESS;
    }

    private function getDatabasePath(Connection $connection): ?string
    {
        $params = $connection->getParams();
        $path = $params['path'] ?? $params['url'] ?? null;
        
        if ($path && str_starts_with($path, 'sqlite:///')) {
            $path = substr($path, 10); // Enlever "sqlite:///"
            // Remplacer %kernel.project_dir% si présent
            if (str_contains($path, '%kernel.project_dir%')) {
                $projectDir = $this->parameterBag->get('kernel.project_dir');
                $path = str_replace('%kernel.project_dir%', $projectDir, $path);
            }
            return $path;
        }
        
        return null;
    }
}
