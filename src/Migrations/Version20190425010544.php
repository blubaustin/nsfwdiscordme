<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190425010544 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE server_team_member ADD user_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE server_team_member ADD CONSTRAINT FK_4E9F447AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4E9F447AA76ED395 ON server_team_member (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE server_team_member DROP FOREIGN KEY FK_4E9F447AA76ED395');
        $this->addSql('DROP INDEX IDX_4E9F447AA76ED395 ON server_team_member');
        $this->addSql('ALTER TABLE server_team_member DROP user_id');
    }
}
