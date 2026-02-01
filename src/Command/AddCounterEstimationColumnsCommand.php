<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-counter-estimation-columns',
    description: 'Ajoute les colonnes compteur_fin_estime et date_releve_fin à la table facturation_compteur'
)]
class AddCounterEstimationColumnsCommand extends Command
{
    public function __construct(
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Vérifier si les colonnes existent déjà
            $schemaManager = $this->connection->createSchemaManager();
            $columns = $schemaManager->listTableColumns('facturation_compteur');
            $columnNames = array_map(fn($col) => $col->getName(), $columns);

            if (!in_array('compteur_fin_estime', $columnNames)) {
                $io->info('Ajout de la colonne compteur_fin_estime...');
                $this->connection->executeStatement(
                    'ALTER TABLE facturation_compteur ADD COLUMN compteur_fin_estime BOOLEAN DEFAULT 0 NOT NULL'
                );
                $io->success('Colonne compteur_fin_estime ajoutée avec succès.');
            } else {
                $io->info('La colonne compteur_fin_estime existe déjà.');
            }

            if (!in_array('date_releve_fin', $columnNames)) {
                $io->info('Ajout de la colonne date_releve_fin...');
                $this->connection->executeStatement(
                    'ALTER TABLE facturation_compteur ADD COLUMN date_releve_fin DATETIME DEFAULT NULL'
                );
                $io->success('Colonne date_releve_fin ajoutée avec succès.');
            } else {
                $io->info('La colonne date_releve_fin existe déjà.');
            }

            $io->success('Toutes les colonnes sont présentes.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
