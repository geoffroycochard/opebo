<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240726143558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176591CC992');
        $this->addSql('ALTER TABLE sector DROP FOREIGN KEY FK_4BA3D9E88565851');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9DE95C867');
        $this->addSql('DROP TABLE sector');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP INDEX IDX_34DCD176591CC992 ON person');
        $this->addSql('ALTER TABLE person ADD study_level SMALLINT DEFAULT NULL, CHANGE course_id establishment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1768565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id)');
        $this->addSql('CREATE INDEX IDX_34DCD1768565851 ON person (establishment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sector (id INT AUTO_INCREMENT NOT NULL, establishment_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_4BA3D9E88565851 (establishment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, sector_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_169E6FB9DE95C867 (sector_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sector ADD CONSTRAINT FK_4BA3D9E88565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1768565851');
        $this->addSql('DROP INDEX IDX_34DCD1768565851 ON person');
        $this->addSql('ALTER TABLE person DROP study_level, CHANGE establishment_id course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_34DCD176591CC992 ON person (course_id)');
    }
}
