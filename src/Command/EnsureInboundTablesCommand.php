<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:inbound:ensure-tables',
    description: 'Crée les tables inbound_alert et inbound_alert_attachment si elles n\'existent pas (après échec migration en post-check).',
)]
final class EnsureInboundTablesCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->tableExists('inbound_alert')) {
            $io->success('Les tables inbound_alert existent déjà. Rien à faire.');
            return Command::SUCCESS;
        }

        $io->section('Création des tables inbound_alert et inbound_alert_attachment');

        $sqls = [
            "CREATE TABLE inbound_alert (
                id INT AUTO_INCREMENT NOT NULL,
                message_id VARCHAR(255) DEFAULT NULL,
                subject VARCHAR(500) DEFAULT NULL,
                from_email VARCHAR(255) DEFAULT NULL,
                received_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                severity VARCHAR(20) DEFAULT NULL,
                body LONGTEXT DEFAULT NULL,
                status VARCHAR(30) NOT NULL,
                error_message LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                processed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX uniq_inbound_alert_message_id (message_id),
                INDEX idx_inbound_alert_status (status),
                INDEX idx_inbound_alert_received_at (received_at),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB",
            "CREATE TABLE inbound_alert_attachment (
                id INT AUTO_INCREMENT NOT NULL,
                inbound_alert_id INT NOT NULL,
                original_name VARCHAR(500) NOT NULL,
                mime_type VARCHAR(100) DEFAULT NULL,
                size INT DEFAULT NULL,
                sha256 VARCHAR(64) NOT NULL,
                stored_path VARCHAR(500) NOT NULL,
                stored_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX uniq_inbound_alert_attachment_sha256 (sha256),
                INDEX idx_inbound_alert_attachment_alert (inbound_alert_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB",
        ];

        foreach ($sqls as $sql) {
            $this->connection->executeStatement($sql);
            $io->writeln('  <info>Table créée.</info>');
        }

        try {
            $this->connection->executeStatement(
                'ALTER TABLE inbound_alert_attachment ADD CONSTRAINT FK_inbound_alert_attachment_alert FOREIGN KEY (inbound_alert_id) REFERENCES inbound_alert (id) ON DELETE CASCADE'
            );
            $io->writeln('  <info>Contrainte FK ajoutée.</info>');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'already exists')) {
                $io->writeln('  <comment>Contrainte FK déjà présente.</comment>');
            } else {
                throw $e;
            }
        }

        $io->success('Tables inbound_alert prêtes. L’API /api/inbound/mail/alert peut recevoir les alertes du mail-fetcher.');
        return Command::SUCCESS;
    }

    private function tableExists(string $tableName): bool
    {
        $result = $this->connection->fetchOne(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
            [$tableName],
        );
        return $result !== false;
    }
}
