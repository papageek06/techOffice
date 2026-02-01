<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128130224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facturation_compteur ADD compteur_fin_estime BOOLEAN DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE facturation_compteur ADD date_releve_fin DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__facturation_compteur AS SELECT id, compteur_debut_noir, compteur_fin_noir, compteur_debut_couleur, compteur_fin_couleur, source_debut, source_fin, facturation_periode_id, affectation_materiel_id FROM facturation_compteur');
        $this->addSql('DROP TABLE facturation_compteur');
        $this->addSql('CREATE TABLE facturation_compteur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, compteur_debut_noir INTEGER NOT NULL, compteur_fin_noir INTEGER NOT NULL, compteur_debut_couleur INTEGER DEFAULT NULL, compteur_fin_couleur INTEGER DEFAULT NULL, source_debut VARCHAR(255) NOT NULL, source_fin VARCHAR(255) NOT NULL, facturation_periode_id INTEGER NOT NULL, affectation_materiel_id INTEGER NOT NULL, CONSTRAINT FK_5E25276FC2C1E85D FOREIGN KEY (facturation_periode_id) REFERENCES facturation_periode (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5E25276F93B880DA FOREIGN KEY (affectation_materiel_id) REFERENCES affectation_materiel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO facturation_compteur (id, compteur_debut_noir, compteur_fin_noir, compteur_debut_couleur, compteur_fin_couleur, source_debut, source_fin, facturation_periode_id, affectation_materiel_id) SELECT id, compteur_debut_noir, compteur_fin_noir, compteur_debut_couleur, compteur_fin_couleur, source_debut, source_fin, facturation_periode_id, affectation_materiel_id FROM __temp__facturation_compteur');
        $this->addSql('DROP TABLE __temp__facturation_compteur');
        $this->addSql('CREATE INDEX idx_facturation_periode ON facturation_compteur (facturation_periode_id)');
        $this->addSql('CREATE INDEX idx_affectation_materiel ON facturation_compteur (affectation_materiel_id)');
    }
}
