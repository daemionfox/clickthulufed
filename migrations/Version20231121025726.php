<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231121025726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypt_key (id INT AUTO_INCREMENT NOT NULL, data LONGBLOB DEFAULT NULL, createdon DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comic ADD publickey_id INT DEFAULT NULL, ADD privatekey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comic ADD CONSTRAINT FK_5B7EA5AA768306AF FOREIGN KEY (publickey_id) REFERENCES crypt_key (id)');
        $this->addSql('ALTER TABLE comic ADD CONSTRAINT FK_5B7EA5AAAB65E550 FOREIGN KEY (privatekey_id) REFERENCES crypt_key (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5B7EA5AA768306AF ON comic (publickey_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5B7EA5AAAB65E550 ON comic (privatekey_id)');
        $this->addSql('ALTER TABLE user ADD publickey_id INT DEFAULT NULL, ADD privatekey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649768306AF FOREIGN KEY (publickey_id) REFERENCES crypt_key (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AB65E550 FOREIGN KEY (privatekey_id) REFERENCES crypt_key (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649768306AF ON user (publickey_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AB65E550 ON user (privatekey_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comic DROP FOREIGN KEY FK_5B7EA5AA768306AF');
        $this->addSql('ALTER TABLE comic DROP FOREIGN KEY FK_5B7EA5AAAB65E550');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649768306AF');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649AB65E550');
        $this->addSql('DROP TABLE crypt_key');
        $this->addSql('DROP INDEX UNIQ_5B7EA5AA768306AF ON comic');
        $this->addSql('DROP INDEX UNIQ_5B7EA5AAAB65E550 ON comic');
        $this->addSql('ALTER TABLE comic DROP publickey_id, DROP privatekey_id');
        $this->addSql('DROP INDEX UNIQ_8D93D649768306AF ON `user`');
        $this->addSql('DROP INDEX UNIQ_8D93D649AB65E550 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP publickey_id, DROP privatekey_id');
    }
}
