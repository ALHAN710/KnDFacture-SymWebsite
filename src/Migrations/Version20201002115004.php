<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201002115004 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stock_movement ADD commercial_sheet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5371614E4 FOREIGN KEY (commercial_sheet_id) REFERENCES commercial_sheet (id)');
        $this->addSql('CREATE INDEX IDX_BB1BC1B5371614E4 ON stock_movement (commercial_sheet_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5371614E4');
        $this->addSql('DROP INDEX IDX_BB1BC1B5371614E4 ON stock_movement');
        $this->addSql('ALTER TABLE stock_movement DROP commercial_sheet_id');
    }
}
