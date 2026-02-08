<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\ProcessInboundEventMessage;
use App\Repository\InboundEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:inbound-events:reprocess',
    description: 'Republie les événements inbound vers Messenger (ex: status=failed, provider=printaudit_fm)',
)]
final class ReprocessInboundEventsCommand extends Command
{
    public function __construct(
        private readonly InboundEventRepository $inboundEventRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'Filtrer par status (ex: failed)', 'failed')
            ->addOption('provider', 'p', InputOption::VALUE_OPTIONAL, 'Filtrer par provider (ex: printaudit_fm)', null)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Nombre max d\'événements à republier', '500');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $status = $input->getOption('status');
        $provider = $input->getOption('provider');
        $limit = (int) $input->getOption('limit');

        $events = $this->inboundEventRepository->findByStatusAndProvider($status, $provider, $limit);
        if ($events === []) {
            $io->info('Aucun événement à retraiter.');
            return Command::SUCCESS;
        }

        $io->title(sprintf('Reprocess %d événement(s) (status=%s, provider=%s)', count($events), $status ?? 'all', $provider ?? 'all'));
        foreach ($events as $event) {
            $this->messageBus->dispatch(new ProcessInboundEventMessage($event->getId()));
        }
        $io->success(sprintf('%d message(s) envoyé(s) au transport async.', count($events)));

        return Command::SUCCESS;
    }
}
