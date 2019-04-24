<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190424164526 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE banned_server (id INT UNSIGNED AUTO_INCREMENT NOT NULL, discord_id BIGINT UNSIGNED NOT NULL, reason VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_63C707AC43349DE (discord_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE banned_word (id INT UNSIGNED AUTO_INCREMENT NOT NULL, word VARCHAR(60) DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_1EC7C5FDC3F17511 (word), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE banned_user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, discord_username VARCHAR(32) DEFAULT NULL, discord_discriminator VARCHAR(4) DEFAULT NULL, reason VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_50A566A54A3132F3D019B0D (discord_username, discord_discriminator), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE banned_server');
        $this->addSql('DROP TABLE banned_word');
        $this->addSql('DROP TABLE banned_user');
    }
}
