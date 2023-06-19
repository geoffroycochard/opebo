<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230619213527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE domain (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lead_domain (lead_id INT NOT NULL, domain_id INT NOT NULL, INDEX IDX_DAC8677755458D (lead_id), INDEX IDX_DAC86777115F0EE5 (domain_id), PRIMARY KEY(lead_id, domain_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lead_domain ADD CONSTRAINT FK_DAC8677755458D FOREIGN KEY (lead_id) REFERENCES lead (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_domain ADD CONSTRAINT FK_DAC86777115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead ADD domain LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lead_domain DROP FOREIGN KEY FK_DAC8677755458D');
        $this->addSql('ALTER TABLE lead_domain DROP FOREIGN KEY FK_DAC86777115F0EE5');
        $this->addSql('DROP TABLE domain');
        $this->addSql('DROP TABLE lead_domain');
        $this->addSql('ALTER TABLE lead DROP domain');
    }
}
