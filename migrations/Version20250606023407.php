<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606023407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijados ADD CONSTRAINT FK_DA6EE8C4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE complemento_oddun CHANGE id id INT NOT NULL, CHANGE patakis patakis LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE consultations ADD CONSTRAINT FK_242D8F53A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE odduns CHANGE name name LONGTEXT NOT NULL, CHANGE alt_names alt_names LONGTEXT NOT NULL, CHANGE nace nace LONGTEXT NOT NULL, CHANGE frases frases LONGTEXT NOT NULL, CHANGE ire ire LONGTEXT NOT NULL, CHANGE osogbo osogbo LONGTEXT NOT NULL, CHANGE bin bin LONGTEXT NOT NULL, CHANGE historia historia LONGTEXT NOT NULL, CHANGE refranes refranes LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE odduns_new CHANGE name name LONGTEXT NOT NULL, CHANGE alt_names alt_names LONGTEXT NOT NULL, CHANGE refranes refranes LONGTEXT NOT NULL, CHANGE ire ire LONGTEXT NOT NULL, CHANGE osogbo osogbo LONGTEXT NOT NULL, CHANGE historia historia LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE odduns_old CHANGE name name LONGTEXT NOT NULL, CHANGE nace nace LONGTEXT NOT NULL, CHANGE refr refr LONGTEXT NOT NULL, CHANGE bin bin LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pasos_awo CHANGE uid uid LONGTEXT NOT NULL, CHANGE titulo titulo LONGTEXT NOT NULL, CHANGE padre padre LONGTEXT DEFAULT NULL, CHANGE contenido contenido LONGTEXT NOT NULL, CHANGE date_added date_added DATETIME NOT NULL, CHANGE date_updated date_updated DATETIME NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE complemento_oddun CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE patakis patakis MEDIUMTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE odduns CHANGE name name TEXT NOT NULL, CHANGE alt_names alt_names TEXT NOT NULL, CHANGE nace nace TEXT NOT NULL, CHANGE frases frases TEXT NOT NULL, CHANGE ire ire TEXT NOT NULL, CHANGE osogbo osogbo TEXT NOT NULL, CHANGE bin bin TEXT NOT NULL, CHANGE historia historia TEXT NOT NULL, CHANGE refranes refranes TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE odduns_new CHANGE name name TEXT NOT NULL, CHANGE alt_names alt_names TEXT NOT NULL, CHANGE refranes refranes TEXT NOT NULL, CHANGE ire ire TEXT NOT NULL, CHANGE osogbo osogbo TEXT NOT NULL, CHANGE historia historia TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE odduns_old CHANGE name name TEXT NOT NULL, CHANGE nace nace TEXT NOT NULL, CHANGE refr refr TEXT NOT NULL, CHANGE bin bin TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pasos_awo CHANGE uid uid TEXT NOT NULL, CHANGE titulo titulo TEXT NOT NULL, CHANGE padre padre TEXT DEFAULT NULL, CHANGE contenido contenido LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE date_added date_added DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE date_updated date_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ahijados DROP FOREIGN KEY FK_DA6EE8C4A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE consultations DROP FOREIGN KEY FK_242D8F53A76ED395
        SQL);
    }
}
