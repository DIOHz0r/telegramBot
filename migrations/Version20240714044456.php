<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240714044456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, service, environment, name FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER NOT NULL, service VARCHAR(255) DEFAULT NULL, environment VARCHAR(50) DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO channel (id, service, environment, name) SELECT id, service, environment, name FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, service, environment, name FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, service VARCHAR(255) DEFAULT NULL, environment VARCHAR(50) DEFAULT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO channel (id, service, environment, name) SELECT id, service, environment, name FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
    }
}
