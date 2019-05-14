<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514022159 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bump_period_vote DROP FOREIGN KEY FK_5F2C7F3B11917174');
        $this->addSql('DROP TABLE bump_period');
        $this->addSql('DROP TABLE bump_period_vote');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bump_period (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, UNIQUE INDEX UNIQ_D3C40199AA9E377A (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE bump_period_vote (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, bump_period_id BIGINT UNSIGNED NOT NULL, server_id BIGINT UNSIGNED NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_5F2C7F3BA76ED395 (user_id), INDEX IDX_5F2C7F3B1844E6B7 (server_id), UNIQUE INDEX UNIQ_5F2C7F3BA76ED395119171741844E6B7 (user_id, bump_period_id, server_id), INDEX IDX_5F2C7F3B11917174 (bump_period_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3B11917174 FOREIGN KEY (bump_period_id) REFERENCES bump_period (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3B1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
