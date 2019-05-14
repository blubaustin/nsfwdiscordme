<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514101317 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX enabled_public_idx ON server');
        $this->addSql('CREATE INDEX enabled_public_premium_idx ON server (is_enabled, is_public, premium_status)');
        $this->addSql('CREATE INDEX enabled_public_idx ON server (is_enabled, is_public)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX enabled_public_premium_idx ON server');
        $this->addSql('DROP INDEX enabled_public_idx ON server');
        $this->addSql('CREATE INDEX enabled_public_idx ON server (is_enabled, is_public, premium_status)');
    }
}
