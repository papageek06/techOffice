<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rendre la colonne intervention.facturable nullable.
 * null = non validé par l'admin, true = à facturer, false = ne pas facturer.
 */
final class Version20260128180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Intervention: facturable nullable (null = en attente validation admin)';
    }

    public function up(Schema $schema): void
    {
        $isSqlite = $this->connection->getDatabasePlatform() instanceof SQLitePlatform;

        if ($isSqlite) {
            $this->addSql('PRAGMA foreign_keys = OFF');
            $this->addSql('CREATE TEMPORARY TABLE __intervention_backup AS SELECT id, imprimante_id, utilisateur_id, date_creation, date_intervention, type_intervention, statut, description, temps_facturable_minutes, temps_reel_minutes, facturable, stock_applique FROM intervention');
            $this->addSql('DROP TABLE intervention');
            $this->addSql('CREATE TABLE intervention (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, imprimante_id INTEGER NOT NULL, utilisateur_id INTEGER NOT NULL, date_creation DATETIME NOT NULL, date_intervention DATETIME DEFAULT NULL, type_intervention VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, description CLOB NOT NULL, temps_facturable_minutes INTEGER NOT NULL, temps_reel_minutes INTEGER DEFAULT NULL, facturable BOOLEAN DEFAULT NULL, stock_applique BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT FK_D11814AB1CA0A76 FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D11814ABFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO intervention (id, imprimante_id, utilisateur_id, date_creation, date_intervention, type_intervention, statut, description, temps_facturable_minutes, temps_reel_minutes, facturable, stock_applique) SELECT id, imprimante_id, utilisateur_id, date_creation, date_intervention, type_intervention, statut, description, temps_facturable_minutes, temps_reel_minutes, facturable, stock_applique FROM __intervention_backup');
            $this->addSql('DROP TABLE __intervention_backup');
            $this->addSql('CREATE INDEX IDX_D11814AB1CA0A76 ON intervention (imprimante_id)');
            $this->addSql('CREATE INDEX IDX_D11814ABFB88E14F ON intervention (utilisateur_id)');
            $this->addSql('PRAGMA foreign_keys = ON');
        } else {
            $this->addSql('ALTER TABLE intervention MODIFY facturable TINYINT(1) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $isSqlite = $this->connection->getDatabasePlatform() instanceof SQLitePlatform;

        if ($isSqlite) {
            $this->addSql('PRAGMA foreign_keys = OFF');
            $this->addSql('CREATE TEMPORARY TABLE __intervention_backup AS SELECT id, imprimante_id, utilisateur_id, date_creation, date_intervention, type_intervention, statut, description, temps_facturable_minutes, temps_reel_minutes, CASE WHEN facturable IS NULL THEN 1 ELSE facturable END AS facturable, stock_applique FROM intervention');
            $this->addSql('DROP TABLE intervention');
            $this->addSql('CREATE TABLE intervention (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, imprimante_id INTEGER NOT NULL, utilisateur_id INTEGER NOT NULL, date_creation DATETIME NOT NULL, date_intervention DATETIME DEFAULT NULL, type_intervention VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, description CLOB NOT NULL, temps_facturable_minutes INTEGER NOT NULL, temps_reel_minutes INTEGER DEFAULT NULL, facturable BOOLEAN DEFAULT 1 NOT NULL, stock_applique BOOLEAN DEFAULT 0 NOT NULL, CONSTRAINT FK_D11814AB1CA0A76 FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D11814ABFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO intervention (id, imprimante_id, utilisateur_id, date_creation, date_intervention, type_intervention, statut, description, temps_facturable_minutes, temps_reel_minutes, facturable, stock_applique) SELECT id, imprimante_id, utilisateur_id, date_creation, date_intervention, type_intervention, statut, description, temps_facturable_minutes, temps_reel_minutes, facturable, stock_applique FROM __intervention_backup');
            $this->addSql('DROP TABLE __intervention_backup');
            $this->addSql('CREATE INDEX IDX_D11814AB1CA0A76 ON intervention (imprimante_id)');
            $this->addSql('CREATE INDEX IDX_D11814ABFB88E14F ON intervention (utilisateur_id)');
            $this->addSql('PRAGMA foreign_keys = ON');
        } else {
            $this->addSql('ALTER TABLE intervention MODIFY facturable TINYINT(1) DEFAULT 1 NOT NULL');
        }
    }
}
