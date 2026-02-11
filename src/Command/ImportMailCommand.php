<?php

namespace App\Command;

use App\Service\InboundMailImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mail:import',
    description: 'Importe les emails non lus depuis la boîte IMAP et sauvegarde les pièces jointes',
)]
class ImportMailCommand extends Command
{
    public function __construct(
        private InboundMailImporter $importer,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Nombre maximum d\'emails à traiter',
                50
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Mode simulation (ne sauvegarde rien)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Mode DRY-RUN activé : aucune modification ne sera effectuée');
        }

        // Créer un verrou fichier pour éviter les exécutions simultanées
        $lockFile = $this->projectDir . '/var/lock/mail-import.lock';
        $lockDir = dirname($lockFile);
        
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0755, true);
        }

        $lockHandle = fopen($lockFile, 'w');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            $io->error('Une autre instance de la commande est déjà en cours d\'exécution.');
            if ($lockHandle) {
                fclose($lockHandle);
            }
            return Command::FAILURE;
        }

        fwrite($lockHandle, getmypid() . "\n" . date('Y-m-d H:i:s'));
        fflush($lockHandle);

        try {
            $io->title('Import des emails IMAP');

            $stats = $this->importer->importUnseenEmails($limit, $dryRun);

            $io->section('Résultats');
            $io->table(
                ['Métrique', 'Valeur'],
                [
                    ['Emails scannés', $stats['scanned']],
                    ['Emails importés', $stats['imported']],
                    ['Emails ignorés (doublons)', $stats['skipped']],
                    ['Erreurs', $stats['errors']],
                    ['Pièces jointes sauvegardées', $stats['attachments']],
                ]
            );

            if ($stats['errors'] > 0) {
                $io->warning(sprintf('%d erreur(s) rencontrée(s). Consultez les logs pour plus de détails.', $stats['errors']));
            }

            if ($stats['scanned'] === 0) {
                $io->info('Aucun email non lu trouvé.');
            } elseif ($stats['imported'] > 0 || $stats['skipped'] > 0) {
                $io->success('Import terminé avec succès.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Erreur lors de l\'import: %s', $e->getMessage()));
            $io->note('Consultez les logs pour plus de détails.');
            return Command::FAILURE;
        } finally {
            if (isset($lockHandle)) {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
                if (file_exists($lockFile)) {
                    @unlink($lockFile);
                }
            }
        }
    }
}
