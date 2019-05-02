<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190502131029 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE purchase_period (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, purchase_id BIGINT UNSIGNED NOT NULL, date_created DATETIME NOT NULL, date_begins DATETIME NOT NULL, date_expires DATETIME NOT NULL, UNIQUE INDEX UNIQ_1ED71C9B558FBEB9 (purchase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_period ADD CONSTRAINT FK_1ED71C9B558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE purchase_period');
    }
}
