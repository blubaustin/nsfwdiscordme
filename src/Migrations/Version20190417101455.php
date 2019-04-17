<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190417101455 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE server (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, discord_id BIGINT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, icon VARCHAR(64) DEFAULT NULL, banner VARCHAR(64) DEFAULT NULL, vanity_url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, INDEX IDX_5A6DD5F6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE server ADD CONSTRAINT FK_5A6DD5F6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE guild');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE guild (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, discord_id BIGINT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, icon VARCHAR(64) DEFAULT NULL COLLATE utf8mb4_unicode_ci, banner VARCHAR(64) DEFAULT NULL COLLATE utf8mb4_unicode_ci, vanity_url VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, description LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, title VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_75407DABA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE guild ADD CONSTRAINT FK_75407DABA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE server');
    }
}
