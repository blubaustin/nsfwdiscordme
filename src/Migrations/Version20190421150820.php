<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190421150820 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bump_period_vote (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, bump_period_id BIGINT UNSIGNED DEFAULT NULL, server_id BIGINT UNSIGNED DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_5F2C7F3BA76ED395 (user_id), UNIQUE INDEX UNIQ_5F2C7F3B11917174 (bump_period_id), UNIQUE INDEX UNIQ_5F2C7F3B1844E6B7 (server_id), UNIQUE INDEX vote_index (user_id, bump_period_id, server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3B11917174 FOREIGN KEY (bump_period_id) REFERENCES bump_period (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3B1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE bump_period_vote');
    }
}
