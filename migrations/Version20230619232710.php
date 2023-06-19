<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230619232710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sponsorship (id INT AUTO_INCREMENT NOT NULL, request_id INT NOT NULL, proposal_id INT NOT NULL, score DOUBLE PRECISION NOT NULL, resume LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_C0F10CD4427EB8A5 (request_id), INDEX IDX_C0F10CD4F4792058 (proposal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sponsorship ADD CONSTRAINT FK_C0F10CD4427EB8A5 FOREIGN KEY (request_id) REFERENCES lead (id)');
        $this->addSql('ALTER TABLE sponsorship ADD CONSTRAINT FK_C0F10CD4F4792058 FOREIGN KEY (proposal_id) REFERENCES lead (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sponsorship DROP FOREIGN KEY FK_C0F10CD4427EB8A5');
        $this->addSql('ALTER TABLE sponsorship DROP FOREIGN KEY FK_C0F10CD4F4792058');
        $this->addSql('DROP TABLE sponsorship');
    }
}
