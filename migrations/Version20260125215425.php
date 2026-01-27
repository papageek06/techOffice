<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260125215425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des tables pour la gestion des contrats et facturation par période';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affectation_materiel (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, type_affectation VARCHAR(255) NOT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, contrat_ligne_id INT NOT NULL, imprimante_id INT NOT NULL, INDEX idx_contrat_ligne (contrat_ligne_id), INDEX idx_imprimante (imprimante_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(100) NOT NULL, type_contrat VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INT NOT NULL, INDEX IDX_6034999319EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat_ligne (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, periodicite VARCHAR(255) NOT NULL, prochaine_facturation DATE NOT NULL, prix_fixe NUMERIC(10, 2) DEFAULT NULL, prix_page_noir NUMERIC(10, 4) DEFAULT NULL, prix_page_couleur NUMERIC(10, 4) DEFAULT NULL, pages_incluses_noir INT DEFAULT NULL, pages_incluses_couleur INT DEFAULT NULL, actif TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, contrat_id INT NOT NULL, site_id INT NOT NULL, INDEX IDX_ADB2B2451823061F (contrat_id), INDEX IDX_ADB2B245F6BD1646 (site_id), INDEX idx_prochaine_facturation (prochaine_facturation), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facturation_compteur (id INT AUTO_INCREMENT NOT NULL, compteur_debut_noir INT NOT NULL, compteur_fin_noir INT NOT NULL, compteur_debut_couleur INT DEFAULT NULL, compteur_fin_couleur INT DEFAULT NULL, source_debut VARCHAR(255) NOT NULL, source_fin VARCHAR(255) NOT NULL, facturation_periode_id INT NOT NULL, affectation_materiel_id INT NOT NULL, INDEX idx_facturation_periode (facturation_periode_id), INDEX idx_affectation_materiel (affectation_materiel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facturation_periode (id INT AUTO_INCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, statut VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, contrat_ligne_id INT NOT NULL, INDEX idx_contrat_ligne (contrat_ligne_id), INDEX idx_statut (statut), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE affectation_materiel ADD CONSTRAINT FK_43CE11A2E38C942C FOREIGN KEY (contrat_ligne_id) REFERENCES contrat_ligne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE affectation_materiel ADD CONSTRAINT FK_43CE11A21CA0A76 FOREIGN KEY (imprimante_id) REFERENCES imprimante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat ADD CONSTRAINT FK_6034999319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat_ligne ADD CONSTRAINT FK_ADB2B2451823061F FOREIGN KEY (contrat_id) REFERENCES contrat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contrat_ligne ADD CONSTRAINT FK_ADB2B245F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facturation_compteur ADD CONSTRAINT FK_5E25276FC2C1E85D FOREIGN KEY (facturation_periode_id) REFERENCES facturation_periode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facturation_compteur ADD CONSTRAINT FK_5E25276F93B880DA FOREIGN KEY (affectation_materiel_id) REFERENCES affectation_materiel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facturation_periode ADD CONSTRAINT FK_D867C1EBE38C942C FOREIGN KEY (contrat_ligne_id) REFERENCES contrat_ligne (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affectation_materiel DROP FOREIGN KEY FK_43CE11A2E38C942C');
        $this->addSql('ALTER TABLE affectation_materiel DROP FOREIGN KEY FK_43CE11A21CA0A76');
        $this->addSql('ALTER TABLE contrat DROP FOREIGN KEY FK_6034999319EB6921');
        $this->addSql('ALTER TABLE contrat_ligne DROP FOREIGN KEY FK_ADB2B2451823061F');
        $this->addSql('ALTER TABLE contrat_ligne DROP FOREIGN KEY FK_ADB2B245F6BD1646');
        $this->addSql('ALTER TABLE facturation_compteur DROP FOREIGN KEY FK_5E25276FC2C1E85D');
        $this->addSql('ALTER TABLE facturation_compteur DROP FOREIGN KEY FK_5E25276F93B880DA');
        $this->addSql('ALTER TABLE facturation_periode DROP FOREIGN KEY FK_D867C1EBE38C942C');
        $this->addSql('DROP TABLE affectation_materiel');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE contrat_ligne');
        $this->addSql('DROP TABLE facturation_compteur');
        $this->addSql('DROP TABLE facturation_periode');
    }
}
