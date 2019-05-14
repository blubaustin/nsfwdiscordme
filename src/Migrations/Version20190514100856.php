<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514100856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE server_follow');
        $this->addSql('DROP INDEX enabled_public_bump_points_idx ON server');
        $this->addSql('DROP INDEX enabled_public_members_online_idx ON server');
        $this->addSql('CREATE INDEX enabled_public_bump_points_idx ON server (is_enabled, is_public, premium_status, bump_points, date_bumped)');
        $this->addSql('CREATE INDEX enabled_public_members_online_idx ON server (is_enabled, is_public, premium_status, members_online)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE server_follow (id INT UNSIGNED AUTO_INCREMENT NOT NULL, server_id BIGINT UNSIGNED DEFAULT NULL, user_id INT UNSIGNED NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_11487DB6A76ED395 (user_id), INDEX IDX_11487DB61844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE server_follow ADD CONSTRAINT FK_11487DB61844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE server_follow ADD CONSTRAINT FK_11487DB6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX enabled_public_bump_points_idx ON server');
        $this->addSql('DROP INDEX enabled_public_members_online_idx ON server');
        $this->addSql('CREATE INDEX enabled_public_bump_points_idx ON server (is_enabled, is_public, bump_points, premium_status)');
        $this->addSql('CREATE INDEX enabled_public_members_online_idx ON server (is_enabled, is_public, members_online, premium_status)');
    }
}
