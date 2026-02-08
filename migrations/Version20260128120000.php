<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260128120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InboundEvent + PrinterExternalRef pour webhooks / alertes PrintAudit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE printer_external_ref (id INT AUTO_INCREMENT NOT NULL, imprimante_id INT NOT NULL, provider VARCHAR(80) NOT NULL, external_id VARCHAR(255) NOT NULL, last_seen_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_printer_external_ref_imprimante (imprimante_id), UNIQUE INDEX uniq_printer_external_ref_provider_external (provider, external_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE printer_external_ref ADD CONSTRAINT FK_printer_external_ref_imprimante FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE inbound_event (id INT AUTO_INCREMENT NOT NULL, imprimante_id INT DEFAULT NULL, provider VARCHAR(80) NOT NULL, endpoint VARCHAR(120) NOT NULL, received_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', contentType VARCHAR(255) DEFAULT NULL, headers JSON DEFAULT NULL, payload_raw LONGTEXT NOT NULL, payload_json JSON DEFAULT NULL, status VARCHAR(30) NOT NULL, parse_error LONGTEXT DEFAULT NULL, fingerprint VARCHAR(64) NOT NULL, meta JSON DEFAULT NULL, INDEX idx_inbound_event_provider (provider), INDEX idx_inbound_event_status (status), INDEX idx_inbound_event_received_at (received_at), UNIQUE INDEX uniq_inbound_event_fingerprint (fingerprint), INDEX IDX_inbound_event_imprimante (imprimante_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inbound_event ADD CONSTRAINT FK_inbound_event_imprimante FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inbound_event DROP FOREIGN KEY FK_inbound_event_imprimante');
        $this->addSql('DROP TABLE inbound_event');
        $this->addSql('ALTER TABLE printer_external_ref DROP FOREIGN KEY FK_printer_external_ref_imprimante');
        $this->addSql('DROP TABLE printer_external_ref');
    }
}
