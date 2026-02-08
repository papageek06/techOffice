<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * M365: oauth_token, sync_state, contact (synchronisation contacts Microsoft 365)
 */
final class Version20260129120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'M365: tables oauth_token, sync_state, contact (sync contacts partagés Graph API)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE oauth_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, provider VARCHAR(50) NOT NULL, access_token LONGTEXT NOT NULL, refresh_token LONGTEXT DEFAULT NULL, expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_oauth_token_user_provider (user_id, provider), INDEX IDX_oauth_token_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sync_state (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, provider VARCHAR(80) NOT NULL, last_sync_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', meta JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_sync_state_user_provider (user_id, provider), INDEX IDX_sync_state_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, source VARCHAR(30) NOT NULL, source_id VARCHAR(255) NOT NULL, display_name VARCHAR(255) DEFAULT NULL, given_name VARCHAR(120) DEFAULT NULL, surname VARCHAR(120) DEFAULT NULL, email1 VARCHAR(255) DEFAULT NULL, email2 VARCHAR(255) DEFAULT NULL, phone_mobile VARCHAR(50) DEFAULT NULL, phone_business VARCHAR(50) DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, address JSON DEFAULT NULL, last_modified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_contact_user_source_source_id (user_id, source, source_id), INDEX IDX_contact_user (user_id), INDEX IDX_contact_source (source), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_token ADD CONSTRAINT FK_oauth_token_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sync_state ADD CONSTRAINT FK_sync_state_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_contact_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_token DROP FOREIGN KEY FK_oauth_token_user');
        $this->addSql('ALTER TABLE sync_state DROP FOREIGN KEY FK_sync_state_user');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_contact_user');
        $this->addSql('DROP TABLE oauth_token');
        $this->addSql('DROP TABLE sync_state');
        $this->addSql('DROP TABLE contact');
    }
}
