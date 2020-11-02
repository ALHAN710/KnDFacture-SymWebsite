<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201028040641 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commercial_sheet ADD enterprise_id INT DEFAULT NULL, CHANGE business_contact_id business_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_sheet ADD CONSTRAINT FK_A84293C3A97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
        $this->addSql('CREATE INDEX IDX_A84293C3A97D1AC3 ON commercial_sheet (enterprise_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commercial_sheet DROP FOREIGN KEY FK_A84293C3A97D1AC3');
        $this->addSql('DROP INDEX IDX_A84293C3A97D1AC3 ON commercial_sheet');
        $this->addSql('ALTER TABLE commercial_sheet DROP enterprise_id, CHANGE business_contact_id business_contact_id INT NOT NULL');
    }
}
