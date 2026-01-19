<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119191106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etat_consommable ADD date_epuisement_noir DATE DEFAULT NULL, ADD date_epuisement_cyan DATE DEFAULT NULL, ADD date_epuisement_magenta DATE DEFAULT NULL, ADD date_epuisement_jaune DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE releve_compteur ADD compteur_fax INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etat_consommable DROP date_epuisement_noir, DROP date_epuisement_cyan, DROP date_epuisement_magenta, DROP date_epuisement_jaune');
        $this->addSql('ALTER TABLE releve_compteur DROP compteur_fax');
    }
}
