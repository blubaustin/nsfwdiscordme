<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190422011415 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE access_token (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, token VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) NOT NULL, scope VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date_expires DATETIME NOT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_B6A2DD68A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, path VARCHAR(255) NOT NULL, adapter VARCHAR(20) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT UNSIGNED AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', discord_id BIGINT UNSIGNED DEFAULT NULL, discord_username VARCHAR(32) DEFAULT NULL, discord_email VARCHAR(255) DEFAULT NULL, discord_avatar VARCHAR(64) DEFAULT NULL, discord_discriminator VARCHAR(4) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bump_period_vote (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, bump_period_id BIGINT UNSIGNED NOT NULL, server_id BIGINT UNSIGNED NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_5F2C7F3BA76ED395 (user_id), INDEX IDX_5F2C7F3B11917174 (bump_period_id), INDEX IDX_5F2C7F3B1844E6B7 (server_id), UNIQUE INDEX UNIQ_5F2C7F3BA76ED395119171741844E6B7 (user_id, bump_period_id, server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE server (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, icon_media_id BIGINT UNSIGNED DEFAULT NULL, banner_media_id BIGINT UNSIGNED DEFAULT NULL, discord_id BIGINT UNSIGNED NOT NULL, slug VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, icon_hash VARCHAR(64) DEFAULT NULL, banner_hash VARCHAR(64) DEFAULT NULL, vanity_url VARCHAR(255) DEFAULT NULL, summary VARCHAR(160) DEFAULT NULL, description LONGTEXT DEFAULT NULL, bump_points INT UNSIGNED NOT NULL, members_online INT UNSIGNED NOT NULL, bot_invite_channel_id BIGINT UNSIGNED DEFAULT NULL, bot_human_check TINYINT(1) NOT NULL, server_password VARCHAR(64) DEFAULT NULL, is_public TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, date_next_bump DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_5A6DD5F6989D9B62 (slug), INDEX IDX_5A6DD5F6A76ED395 (user_id), UNIQUE INDEX UNIQ_5A6DD5F6F78AF24A (icon_media_id), UNIQUE INDEX UNIQ_5A6DD5F6E8A61356 (banner_media_id), INDEX enabled_public_idx (is_enabled, is_public), INDEX enabled_public_bump_points_idx (is_enabled, is_public, bump_points), INDEX enabled_public_members_online_idx (is_enabled, is_public, members_online), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE server_categories (server_id BIGINT UNSIGNED NOT NULL, category_id INT UNSIGNED NOT NULL, INDEX IDX_E83CD6CF1844E6B7 (server_id), INDEX IDX_E83CD6CF12469DE2 (category_id), PRIMARY KEY(server_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE server_tags (server_id BIGINT UNSIGNED NOT NULL, tag_id INT UNSIGNED NOT NULL, INDEX IDX_E43CE5C61844E6B7 (server_id), INDEX IDX_E43CE5C6BAD26311 (tag_id), PRIMARY KEY(server_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, date_updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bump_period (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, UNIQUE INDEX UNIQ_D3C40199AA9E377A (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bump_server_event (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, server_id BIGINT UNSIGNED DEFAULT NULL, ip VARBINARY(16) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_515690541844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD68A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3B11917174 FOREIGN KEY (bump_period_id) REFERENCES bump_period (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bump_period_vote ADD CONSTRAINT FK_5F2C7F3B1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE server ADD CONSTRAINT FK_5A6DD5F6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE server ADD CONSTRAINT FK_5A6DD5F6F78AF24A FOREIGN KEY (icon_media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE server ADD CONSTRAINT FK_5A6DD5F6E8A61356 FOREIGN KEY (banner_media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE server_categories ADD CONSTRAINT FK_E83CD6CF1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE server_categories ADD CONSTRAINT FK_E83CD6CF12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE server_tags ADD CONSTRAINT FK_E43CE5C61844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE server_tags ADD CONSTRAINT FK_E43CE5C6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE bump_server_event ADD CONSTRAINT FK_515690541844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE server DROP FOREIGN KEY FK_5A6DD5F6F78AF24A');
        $this->addSql('ALTER TABLE server DROP FOREIGN KEY FK_5A6DD5F6E8A61356');
        $this->addSql('ALTER TABLE access_token DROP FOREIGN KEY FK_B6A2DD68A76ED395');
        $this->addSql('ALTER TABLE bump_period_vote DROP FOREIGN KEY FK_5F2C7F3BA76ED395');
        $this->addSql('ALTER TABLE server DROP FOREIGN KEY FK_5A6DD5F6A76ED395');
        $this->addSql('ALTER TABLE server_categories DROP FOREIGN KEY FK_E83CD6CF12469DE2');
        $this->addSql('ALTER TABLE bump_period_vote DROP FOREIGN KEY FK_5F2C7F3B1844E6B7');
        $this->addSql('ALTER TABLE server_categories DROP FOREIGN KEY FK_E83CD6CF1844E6B7');
        $this->addSql('ALTER TABLE server_tags DROP FOREIGN KEY FK_E43CE5C61844E6B7');
        $this->addSql('ALTER TABLE bump_server_event DROP FOREIGN KEY FK_515690541844E6B7');
        $this->addSql('ALTER TABLE server_tags DROP FOREIGN KEY FK_E43CE5C6BAD26311');
        $this->addSql('ALTER TABLE bump_period_vote DROP FOREIGN KEY FK_5F2C7F3B11917174');
        $this->addSql('DROP TABLE access_token');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE bump_period_vote');
        $this->addSql('DROP TABLE server');
        $this->addSql('DROP TABLE server_categories');
        $this->addSql('DROP TABLE server_tags');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE bump_period');
        $this->addSql('DROP TABLE bump_server_event');
    }
}
