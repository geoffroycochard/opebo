<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240514071007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT NOT NULL, name VARCHAR(255) NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, shape LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, sector_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_169E6FB9DE95C867 (sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domain (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE establishment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lead (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, language JSON NOT NULL COMMENT \'(DC2Type:json)\', objective JSON NOT NULL COMMENT \'(DC2Type:json)\', status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', discr VARCHAR(255) NOT NULL, INDEX IDX_289161CB217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lead_domain (lead_id INT NOT NULL, domain_id INT NOT NULL, INDEX IDX_DAC8677755458D (lead_id), INDEX IDX_DAC86777115F0EE5 (domain_id), PRIMARY KEY(lead_id, domain_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, gender JSON NOT NULL COMMENT \'(DC2Type:json)\', civility VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', email VARCHAR(255) NOT NULL, birthdate DATE DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_34DCD1768BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sector (id INT AUTO_INCREMENT NOT NULL, establishment_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_4BA3D9E88565851 (establishment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sponsorship (id INT AUTO_INCREMENT NOT NULL, request_id INT NOT NULL, proposal_id INT NOT NULL, score DOUBLE PRECISION NOT NULL, resume JSON NOT NULL COMMENT \'(DC2Type:json)\', status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C0F10CD4427EB8A5 (request_id), INDEX IDX_C0F10CD4F4792058 (proposal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE lead ADD CONSTRAINT FK_289161CB217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE lead_domain ADD CONSTRAINT FK_DAC8677755458D FOREIGN KEY (lead_id) REFERENCES lead (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_domain ADD CONSTRAINT FK_DAC86777115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1768BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE sector ADD CONSTRAINT FK_4BA3D9E88565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id)');
        $this->addSql('ALTER TABLE sponsorship ADD CONSTRAINT FK_C0F10CD4427EB8A5 FOREIGN KEY (request_id) REFERENCES lead (id)');
        $this->addSql('ALTER TABLE sponsorship ADD CONSTRAINT FK_C0F10CD4F4792058 FOREIGN KEY (proposal_id) REFERENCES lead (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9DE95C867');
        $this->addSql('ALTER TABLE lead DROP FOREIGN KEY FK_289161CB217BBB47');
        $this->addSql('ALTER TABLE lead_domain DROP FOREIGN KEY FK_DAC8677755458D');
        $this->addSql('ALTER TABLE lead_domain DROP FOREIGN KEY FK_DAC86777115F0EE5');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1768BAC62AF');
        $this->addSql('ALTER TABLE sector DROP FOREIGN KEY FK_4BA3D9E88565851');
        $this->addSql('ALTER TABLE sponsorship DROP FOREIGN KEY FK_C0F10CD4427EB8A5');
        $this->addSql('ALTER TABLE sponsorship DROP FOREIGN KEY FK_C0F10CD4F4792058');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE domain');
        $this->addSql('DROP TABLE establishment');
        $this->addSql('DROP TABLE lead');
        $this->addSql('DROP TABLE lead_domain');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE sector');
        $this->addSql('DROP TABLE sponsorship');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
