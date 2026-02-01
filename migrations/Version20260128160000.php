<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260128160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute quantite_max à stock_item (stock entreprise : 0 par défaut, max 1).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_item ADD quantite_max INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stock_item DROP COLUMN quantite_max');
    }
}
