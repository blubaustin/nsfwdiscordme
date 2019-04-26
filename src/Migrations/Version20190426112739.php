<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190426112739 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX enabled_public_members_online_idx ON server');
        $this->addSql('DROP INDEX enabled_public_idx ON server');
        $this->addSql('DROP INDEX enabled_public_bump_points_idx ON server');
        $this->addSql('CREATE INDEX enabled_public_members_online_idx ON server (is_enabled, is_public, members_online, premium_status)');
        $this->addSql('CREATE INDEX enabled_public_idx ON server (is_enabled, is_public, premium_status)');
        $this->addSql('CREATE INDEX enabled_public_bump_points_idx ON server (is_enabled, is_public, bump_points, premium_status)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX enabled_public_idx ON server');
        $this->addSql('DROP INDEX enabled_public_bump_points_idx ON server');
        $this->addSql('DROP INDEX enabled_public_members_online_idx ON server');
        $this->addSql('CREATE INDEX enabled_public_idx ON server (is_enabled, is_public)');
        $this->addSql('CREATE INDEX enabled_public_bump_points_idx ON server (is_enabled, is_public, bump_points)');
        $this->addSql('CREATE INDEX enabled_public_members_online_idx ON server (is_enabled, is_public, members_online)');
    }
}
