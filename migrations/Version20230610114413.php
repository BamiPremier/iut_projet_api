<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230610114413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle ALTER longitude TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE salle ALTER latitude TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE salle ALTER altitude TYPE DOUBLE PRECISION');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE salle ALTER longitude TYPE NUMERIC(10, 0)');
        $this->addSql('ALTER TABLE salle ALTER latitude TYPE NUMERIC(10, 0)');
        $this->addSql('ALTER TABLE salle ALTER altitude TYPE NUMERIC(10, 0)');
    }
}
