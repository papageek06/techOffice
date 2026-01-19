<?php

namespace App\Command;

use App\Service\ImportCsvService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:csv',
    description: 'Importe un fichier CSV de relevés d\'imprimantes',
)]
class ImportCsvCommand extends Command
{
    public function __construct(
        private ImportCsvService $importService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Chemin vers le fichier CSV à importer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            $io->error("Le fichier n'existe pas : $filePath");
            return Command::FAILURE;
        }

        $io->title('Import CSV - TechOffice');
        $io->info("Import du fichier : $filePath");

        $io->progressStart();
        $result = $this->importService->import($filePath);
        $io->progressFinish();

        if (!empty($result['errors'])) {
            $io->warning(sprintf('%d erreur(s) rencontrée(s)', count($result['errors'])));
            foreach ($result['errors'] as $error) {
                $io->writeln("  - $error");
            }
        }

        $io->success([
            sprintf('Import terminé avec succès !'),
            sprintf('  ✓ %d ligne(s) importée(s)', $result['success']),
            sprintf('  ⊘ %d ligne(s) ignorée(s)', $result['skipped']),
            sprintf('  ✗ %d erreur(s)', count($result['errors'])),
        ]);

        return Command::SUCCESS;
    }
}
