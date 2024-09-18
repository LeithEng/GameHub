<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240918135846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE debit_card CHANGE expiry_date expiry_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE balance balance NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE user ADD banned TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE debit_card CHANGE expiry_date expiry_date DATETIME NOT NULL, CHANGE balance balance NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE user DROP banned');
    }
}
