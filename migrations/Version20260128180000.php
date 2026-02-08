<?php

declare(strict_types=1);

namespace DoctrineMigrations;

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
        $this->addSql('ALTER TABLE intervention MODIFY facturable TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE intervention MODIFY facturable TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
