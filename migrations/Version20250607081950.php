<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607081950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments ADD client_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A19EB6921 FOREIGN KEY (client_id) REFERENCES ahijados (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41727A19EB6921 ON appointments (client_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6A41727A19EB6921 ON appointments
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments DROP client_id
        SQL);
    }
}
