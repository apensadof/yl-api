<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606171639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ahijado_orisha (ahijado_id INT NOT NULL, orisha_id INT NOT NULL, INDEX IDX_8D5DC2E08F297ABA (ahijado_id), INDEX IDX_8D5DC2E0ABE60AA7 (orisha_id), PRIMARY KEY(ahijado_id, orisha_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ahijado_ceremonia (ahijado_id INT NOT NULL, ceremonia_id INT NOT NULL, INDEX IDX_7E55EC678F297ABA (ahijado_id), INDEX IDX_7E55EC676B84AD97 (ceremonia_id), PRIMARY KEY(ahijado_id, ceremonia_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ceremonias (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, descripcion LONGTEXT NOT NULL, categoria VARCHAR(100) NOT NULL, requisitos LONGTEXT DEFAULT NULL, materiales JSON DEFAULT NULL, procedimiento LONGTEXT DEFAULT NULL, duracion_minutos INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE orishas (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, otros_nombres JSON NOT NULL, dominio LONGTEXT NOT NULL, color VARCHAR(255) NOT NULL, numero INT NOT NULL, atributos JSON NOT NULL, sincretismo VARCHAR(255) NOT NULL, dia VARCHAR(100) NOT NULL, categoria VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_orisha ADD CONSTRAINT FK_8D5DC2E08F297ABA FOREIGN KEY (ahijado_id) REFERENCES ahijados (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_orisha ADD CONSTRAINT FK_8D5DC2E0ABE60AA7 FOREIGN KEY (orisha_id) REFERENCES orishas (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_ceremonia ADD CONSTRAINT FK_7E55EC678F297ABA FOREIGN KEY (ahijado_id) REFERENCES ahijados (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_ceremonia ADD CONSTRAINT FK_7E55EC676B84AD97 FOREIGN KEY (ceremonia_id) REFERENCES ceremonias (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijados ADD orisha_cabeza_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijados ADD CONSTRAINT FK_DA6EE8C4B3238E76 FOREIGN KEY (orisha_cabeza_id) REFERENCES orishas (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DA6EE8C4B3238E76 ON ahijados (orisha_cabeza_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_orisha DROP FOREIGN KEY FK_8D5DC2E08F297ABA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_orisha DROP FOREIGN KEY FK_8D5DC2E0ABE60AA7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_ceremonia DROP FOREIGN KEY FK_7E55EC678F297ABA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijado_ceremonia DROP FOREIGN KEY FK_7E55EC676B84AD97
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ahijado_orisha
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ahijado_ceremonia
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ceremonias
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE orishas
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijados DROP FOREIGN KEY FK_DA6EE8C4B3238E76
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_DA6EE8C4B3238E76 ON ahijados
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijados DROP orisha_cabeza_id
        SQL);
    }
}
