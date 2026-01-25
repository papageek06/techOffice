<?php

namespace App\Command;

use App\Entity\Imprimante;
use App\Enum\PieceRoleModele;
use App\Enum\StatutImprimante;
use App\Repository\ImprimanteRepository;
use App\Service\TonerAlertService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour vérifier les alertes de stock de toner
 */
#[AsCommand(
    name: 'app:toner:check-alerts',
    description: 'Vérifie les alertes de stock bas pour les imprimantes suivies'
)]
class TonerCheckAlertsCommand extends Command
{
    public function __construct(
        private ImprimanteRepository $imprimanteRepository,
        private TonerAlertService $tonerAlertService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Vérification des alertes de stock de toner');

        // Récupérer toutes les imprimantes suivies et actives
        $imprimantes = $this->imprimanteRepository->createQueryBuilder('i')
            ->where('i.suivieParService = true')
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', StatutImprimante::ACTIF)
            ->getQuery()
            ->getResult();

        if (empty($imprimantes)) {
            $io->info('Aucune imprimante suivie trouvée.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Vérification de %d imprimante(s)...', count($imprimantes)));

        $alertes = [];
        $roles = [
            PieceRoleModele::TONER_K,
            PieceRoleModele::TONER_C,
            PieceRoleModele::TONER_M,
            PieceRoleModele::TONER_Y,
            PieceRoleModele::BAC_RECUP,
        ];

        foreach ($imprimantes as $imprimante) {
            foreach ($roles as $role) {
                if ($this->tonerAlertService->shouldAlertDelivery($imprimante, $role)) {
                    $alertes[] = [
                        'imprimante' => $imprimante,
                        'role' => $role,
                    ];
                }
            }
        }

        if (empty($alertes)) {
            $io->success('Aucune alerte détectée.');
            return Command::SUCCESS;
        }

        // Afficher les alertes
        $io->warning(sprintf('%d alerte(s) détectée(s):', count($alertes)));

        $rows = [];
        foreach ($alertes as $alerte) {
            /** @var Imprimante $imprimante */
            $imprimante = $alerte['imprimante'];
            /** @var PieceRoleModele $role */
            $role = $alerte['role'];

            $rows[] = [
                $imprimante->getSite()->getNomSite(),
                $imprimante->getModele()->getReferenceModele(),
                $imprimante->getNumeroSerie(),
                $role->value,
            ];
        }

        $io->table(
            ['Site', 'Modèle', 'N° Série', 'Rôle'],
            $rows
        );

        return Command::SUCCESS;
    }
}
