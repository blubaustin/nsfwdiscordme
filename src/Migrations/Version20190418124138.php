<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190418124138 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE media (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, path VARCHAR(255) NOT NULL, adapter VARCHAR(20) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE server_medias (server_id BIGINT UNSIGNED NOT NULL, media_id BIGINT UNSIGNED NOT NULL, INDEX IDX_6BAE96471844E6B7 (server_id), INDEX IDX_6BAE9647EA9FDD75 (media_id), PRIMARY KEY(server_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE server_medias ADD CONSTRAINT FK_6BAE96471844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE server_medias ADD CONSTRAINT FK_6BAE9647EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE server_categories CHANGE category_id category_id INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE server_medias DROP FOREIGN KEY FK_6BAE9647EA9FDD75');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE server_medias');
        $this->addSql('ALTER TABLE server_categories CHANGE category_id category_id BIGINT UNSIGNED NOT NULL');
    }
}
