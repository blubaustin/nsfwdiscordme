<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190420203524 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE server_tags (server_id BIGINT UNSIGNED NOT NULL, tag_id INT UNSIGNED NOT NULL, INDEX IDX_E43CE5C61844E6B7 (server_id), INDEX IDX_E43CE5C6BAD26311 (tag_id), PRIMARY KEY(server_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE server_tags ADD CONSTRAINT FK_E43CE5C61844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE server_tags ADD CONSTRAINT FK_E43CE5C6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('DROP INDEX search ON server');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE server_tags DROP FOREIGN KEY FK_E43CE5C6BAD26311');
        $this->addSql('DROP TABLE server_tags');
        $this->addSql('DROP TABLE tag');
        $this->addSql('CREATE FULLTEXT INDEX search ON server (name, summary, description)');
    }
}
