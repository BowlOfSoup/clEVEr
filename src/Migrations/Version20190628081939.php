<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190628081939 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE corporation (id INT AUTO_INCREMENT NOT NULL, alliance_id INT DEFAULT NULL, eve_id INT NOT NULL, name VARCHAR(255) NOT NULL, ticker VARCHAR(5) NOT NULL, description LONGTEXT DEFAULT NULL, bulletin LONGTEXT DEFAULT NULL, INDEX IDX_842A568310A0EA3F (alliance_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, corporation_id INT DEFAULT NULL, eve_id INT NOT NULL, name VARCHAR(255) NOT NULL, biography LONGTEXT DEFAULT NULL, discord_auth_token VARCHAR(30) DEFAULT NULL, discord_user_id VARCHAR(30) DEFAULT NULL, access_token VARCHAR(255) NOT NULL, token_expiry_time DATETIME NOT NULL, refresh_token VARCHAR(255) NOT NULL, INDEX IDX_937AB034B2685369 (corporation_id), INDEX i_discord_auth_token (discord_auth_token), INDEX i_discord_user_id (discord_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alliance (id INT AUTO_INCREMENT NOT NULL, eve_id INT NOT NULL, name VARCHAR(255) NOT NULL, ticker VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE corporation ADD CONSTRAINT FK_842A568310A0EA3F FOREIGN KEY (alliance_id) REFERENCES alliance (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034B2685369 FOREIGN KEY (corporation_id) REFERENCES corporation (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034B2685369');
        $this->addSql('ALTER TABLE corporation DROP FOREIGN KEY FK_842A568310A0EA3F');
        $this->addSql('DROP TABLE corporation');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE alliance');
    }
}
