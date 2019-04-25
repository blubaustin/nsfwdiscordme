<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190425010326 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE server_team_member DROP FOREIGN KEY FK_4E9F447AA76ED395');
        $this->addSql('DROP INDEX UNIQ_4E9F447AA76ED3951844E6B7 ON server_team_member');
        $this->addSql('DROP INDEX IDX_4E9F447AA76ED395 ON server_team_member');
        $this->addSql('ALTER TABLE server_team_member ADD discord_username VARCHAR(32) DEFAULT NULL, ADD discord_discriminator VARCHAR(4) DEFAULT NULL, DROP user_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E9F447A1844E6B74A3132F3D019B0D ON server_team_member (server_id, discord_username, discord_discriminator)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_4E9F447A1844E6B74A3132F3D019B0D ON server_team_member');
        $this->addSql('ALTER TABLE server_team_member ADD user_id INT UNSIGNED NOT NULL, DROP discord_username, DROP discord_discriminator');
        $this->addSql('ALTER TABLE server_team_member ADD CONSTRAINT FK_4E9F447AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E9F447AA76ED3951844E6B7 ON server_team_member (user_id, server_id)');
        $this->addSql('CREATE INDEX IDX_4E9F447AA76ED395 ON server_team_member (user_id)');
    }
}
