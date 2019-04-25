<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190424231225 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE server_team_member (id INT UNSIGNED AUTO_INCREMENT NOT NULL, server_id BIGINT UNSIGNED DEFAULT NULL, user_id INT UNSIGNED NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_4E9F447A1844E6B7 (server_id), INDEX IDX_4E9F447AA76ED395 (user_id), UNIQUE INDEX UNIQ_4E9F447AA76ED3951844E6B7 (user_id, server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE server_team_member ADD CONSTRAINT FK_4E9F447A1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE server_team_member ADD CONSTRAINT FK_4E9F447AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE server_team_member');
    }
}
