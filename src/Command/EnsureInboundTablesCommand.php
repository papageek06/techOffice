<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\InboundAlert;
use App\Entity\InboundAlertAttachment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:inbound:ensure-tables',
    description: 'Crée les tables inbound_alert et inbound_alert_attachment à partir des entités Doctrine (si elles n\'existent pas).',
)]
final class EnsureInboundTablesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->em->getConnection();
        if ($this->tableExists($connection, 'inbound_alert')) {
            $io->success('Les tables inbound_alert existent déjà. Rien à faire.');
            return Command::SUCCESS;
        }

        $io->section('Création des tables à partir des entités Doctrine (InboundAlert, InboundAlertAttachment)');

        $schemaTool = new SchemaTool($this->em);
        $metadata = [
            $this->em->getClassMetadata(InboundAlert::class),
            $this->em->getClassMetadata(InboundAlertAttachment::class),
        ];

        $sqls = $schemaTool->getCreateSchemaSql($metadata);

        $allowedTables = ['inbound_alert', 'inbound_alert_attachment'];
        foreach ($sqls as $sql) {
            // Ne créer que nos tables ; ignorer messenger_messages etc. que SchemaTool peut inclure
            $createTable = preg_match('/CREATE TABLE\s+[`"]?(\w+)[`"]?\s+/i', $sql, $m) ? ($m[1] ?? '') : '';
            if (!\in_array($createTable, $allowedTables, true)) {
                continue;
            }
            $connection->executeStatement($sql);
            $io->writeln('  <info>Exécuté :</info> ' . substr($sql, 0, 80) . '…');
        }

        $io->success('Tables créées à partir des entités. L’API /api/inbound/mail/alert peut recevoir les alertes du mail-fetcher.');
        return Command::SUCCESS;
    }

    private function tableExists(mixed $connection, string $tableName): bool
    {
        $result = $connection->fetchOne(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
            [$tableName],
        );
        return $result !== false;
    }
}
