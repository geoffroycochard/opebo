<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230619211925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, gender LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', language LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', objective LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', discr VARCHAR(255) NOT NULL, INDEX IDX_289161CB217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lead ADD CONSTRAINT FK_289161CB217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lead DROP FOREIGN KEY FK_289161CB217BBB47');
        $this->addSql('DROP TABLE lead');
    }
}
