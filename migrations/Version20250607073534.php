<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607073534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F16A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events RENAME INDEX idx_3963fa7ea76ed395 TO IDX_F9E14F16A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD spiritual_level VARCHAR(100) DEFAULT NULL, ADD phone VARCHAR(20) DEFAULT NULL, ADD city VARCHAR(100) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD status VARCHAR(20) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP spiritual_level, DROP phone, DROP city, DROP notes, DROP status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F16A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events RENAME INDEX idx_f9e14f16a76ed395 TO IDX_3963FA7EA76ED395
        SQL);
    }
}
