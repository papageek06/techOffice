<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\OAuthTokenRepository;
use App\Service\M365\M365ContactSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:m365:sync-contacts-shared',
    description: 'Synchronise les contacts M365 partagés (Graph API). Utilisable en cron après connexion manuelle des utilisateurs.',
)]
final class M365SyncContactsCommand extends Command
{
    public function __construct(
        private readonly OAuthTokenRepository $oauthTokenRepository,
        private readonly M365ContactSyncService $syncService,
        private readonly \Doctrine\ORM\EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'Email de l\'utilisateur à synchroniser (sinon tous les utilisateurs connectés M365)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getOption('user');

        $users = $this->getUsersToSync($userEmail);
        if (\count($users) === 0) {
            $io->warning('Aucun utilisateur avec token M365 trouvé. Connectez-vous d\'abord via /m365/login.');
            return Command::SUCCESS;
        }

        $io->progressStart(\count($users));
        $totalUpserted = 0;
        foreach ($users as $user) {
            try {
                $result = $this->syncService->sync($user);
                $totalUpserted += $result['contacts_upserted'];
                $io->progressAdvance();
                $io->text(sprintf('  %s: %d contact(s)', $user->getUserIdentifier(), $result['contacts_upserted']));
            } catch (\Throwable $e) {
                $io->progressAdvance();
                $io->error(sprintf('  %s: %s', $user->getUserIdentifier(), $e->getMessage()));
            }
        }
        $io->progressFinish();
        $io->success(sprintf('Synchronisation terminée: %d contact(s) au total.', $totalUpserted));
        return Command::SUCCESS;
    }

    /** @return list<User> */
    private function getUsersToSync(?string $userEmail): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->innerJoin('App\Entity\OAuthToken', 't', 'WITH', 't.user = u AND t.provider = :provider')
            ->setParameter('provider', 'm365');
        if ($userEmail !== null && $userEmail !== '') {
            $qb->andWhere('u.email = :email')->setParameter('email', $userEmail);
        }
        return $qb->getQuery()->getResult();
    }
}
