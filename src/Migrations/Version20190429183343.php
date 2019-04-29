<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190429183343 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE server_event (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, server_id BIGINT UNSIGNED DEFAULT NULL, ip VARBINARY(16) NOT NULL, event_type SMALLINT NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_9B2F68AE1844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE server_event ADD CONSTRAINT FK_9B2F68AE1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO `server_event` (server_id, ip, date_created, event_type) (select server_id, ip, date_created, 0 from server_join_event)');
        $this->addSql('INSERT INTO `server_event` (server_id, ip, date_created, event_type) (select server_id, ip, date_created, 1 from server_view_event)');
        $this->addSql('INSERT INTO `server_event` (server_id, ip, date_created, event_type) (select server_id, ip, date_created, 2 from server_bump_event)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE server_event');
    }
}
