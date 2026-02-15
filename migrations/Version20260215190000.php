<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tables defaut_imprimante (alertes parsées) et rapport_csv (rapports CSV reçus)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE defaut_imprimante (
            id INT AUTO_INCREMENT NOT NULL,
            inbound_alert_id INT DEFAULT NULL,
            site_nom VARCHAR(255) NOT NULL,
            imprimante_id INT DEFAULT NULL,
            type_defaut VARCHAR(30) NOT NULL,
            message LONGTEXT DEFAULT NULL,
            couleur_toner VARCHAR(20) DEFAULT NULL,
            machine_serial VARCHAR(120) DEFAULT NULL,
            machine_ip VARCHAR(45) DEFAULT NULL,
            machine_nom VARCHAR(120) DEFAULT NULL,
            received_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_defaut_site_nom (site_nom),
            INDEX idx_defaut_type (type_defaut),
            INDEX idx_defaut_received_at (received_at),
            INDEX IDX_defaut_imprimante_id (imprimante_id),
            INDEX IDX_defaut_inbound_alert_id (inbound_alert_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE defaut_imprimante ADD CONSTRAINT FK_defaut_imprimante_imprimante FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE defaut_imprimante ADD CONSTRAINT FK_defaut_imprimante_inbound_alert FOREIGN KEY (inbound_alert_id) REFERENCES inbound_alert (id) ON DELETE SET NULL');

        $this->addSql('CREATE TABLE rapport_csv (
            id INT AUTO_INCREMENT NOT NULL,
            inbound_alert_id INT DEFAULT NULL,
            stored_path VARCHAR(500) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_rapport_csv_created_at (created_at),
            INDEX IDX_rapport_csv_inbound_alert_id (inbound_alert_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE rapport_csv ADD CONSTRAINT FK_rapport_csv_inbound_alert FOREIGN KEY (inbound_alert_id) REFERENCES inbound_alert (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE defaut_imprimante DROP FOREIGN KEY FK_defaut_imprimante_imprimante');
        $this->addSql('ALTER TABLE defaut_imprimante DROP FOREIGN KEY FK_defaut_imprimante_inbound_alert');
        $this->addSql('DROP TABLE defaut_imprimante');
        $this->addSql('ALTER TABLE rapport_csv DROP FOREIGN KEY FK_rapport_csv_inbound_alert');
        $this->addSql('DROP TABLE rapport_csv');
    }
}
