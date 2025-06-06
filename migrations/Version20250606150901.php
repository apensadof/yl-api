<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606150901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE appointments (id INT AUTO_INCREMENT NOT NULL, client_name VARCHAR(100) NOT NULL, type VARCHAR(100) NOT NULL, date DATE NOT NULL, time TIME NOT NULL, duration INT NOT NULL, status VARCHAR(20) DEFAULT 'pendiente' NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_6A41727AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE knowledge_categories (id VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, item_count INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE knowledge_items (id VARCHAR(100) NOT NULL, title VARCHAR(200) NOT NULL, content LONGTEXT NOT NULL, keywords JSON NOT NULL, views INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id VARCHAR(50) NOT NULL, INDEX IDX_C0C528B212469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE knowledge_related (knowledge_id VARCHAR(100) NOT NULL, related_id VARCHAR(100) NOT NULL, INDEX IDX_1F105E1FE7DC6902 (knowledge_id), INDEX IDX_1F105E1F4162C001 (related_id), PRIMARY KEY(knowledge_id, related_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments ADD CONSTRAINT FK_6A41727AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE knowledge_items ADD CONSTRAINT FK_C0C528B212469DE2 FOREIGN KEY (category_id) REFERENCES knowledge_categories (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE knowledge_related ADD CONSTRAINT FK_1F105E1FE7DC6902 FOREIGN KEY (knowledge_id) REFERENCES knowledge_items (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE knowledge_related ADD CONSTRAINT FK_1F105E1F4162C001 FOREIGN KEY (related_id) REFERENCES knowledge_items (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE knowledge_items DROP FOREIGN KEY FK_C0C528B212469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE knowledge_related DROP FOREIGN KEY FK_1F105E1FE7DC6902
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE knowledge_related DROP FOREIGN KEY FK_1F105E1F4162C001
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appointments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE knowledge_categories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE knowledge_items
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE knowledge_related
        SQL);
    }
}
