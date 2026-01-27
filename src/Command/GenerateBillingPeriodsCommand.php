<?php

namespace App\Command;

use App\Service\BillingPeriodGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour générer les périodes de facturation
 */
#[AsCommand(
    name: 'app:billing:generate-periods',
    description: 'Génère les périodes de facturation pour les lignes de contrat éligibles'
)]
class GenerateBillingPeriodsCommand extends Command
{
    public function __construct(
        private readonly BillingPeriodGenerator $billingPeriodGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Date de référence (format: Y-m-d). Par défaut: aujourd\'hui',
                null
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Mode simulation (ne sauvegarde pas)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dateReference = null;
        if ($input->getOption('date')) {
            try {
                $dateReference = new \DateTimeImmutable($input->getOption('date'));
            } catch (\Exception $e) {
                $io->error(sprintf('Date invalide: %s', $input->getOption('date')));
                return Command::FAILURE;
            }
        }

        $io->title('Génération des périodes de facturation');

        if ($dateReference) {
            $io->info(sprintf('Date de référence: %s', $dateReference->format('d/m/Y')));
        } else {
            $io->info('Date de référence: aujourd\'hui');
        }

        if ($input->getOption('dry-run')) {
            $io->warning('Mode simulation activé - aucune donnée ne sera sauvegardée');
        }

        try {
            $periodes = $this->billingPeriodGenerator->generatePeriods($dateReference);

            if (empty($periodes)) {
                $io->success('Aucune période à générer.');
            } else {
                $io->success(sprintf('%d période(s) générée(s)', count($periodes)));

                $table = [];
                foreach ($periodes as $periode) {
                    $table[] = [
                        $periode->getId(),
                        $periode->getContratLigne()->getLibelle(),
                        $periode->getDateDebut()->format('d/m/Y'),
                        $periode->getDateFin()->format('d/m/Y'),
                        $periode->getStatut()->value,
                    ];
                }

                $io->table(
                    ['ID', 'Ligne', 'Début', 'Fin', 'Statut'],
                    $table
                );
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Erreur lors de la génération: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
