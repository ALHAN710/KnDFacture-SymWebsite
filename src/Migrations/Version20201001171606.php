<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201001171606 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commercial_sheet ADD inventory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commercial_sheet ADD CONSTRAINT FK_A84293C39EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id)');
        $this->addSql('CREATE INDEX IDX_A84293C39EEA759 ON commercial_sheet (inventory_id)');
        $this->addSql('ALTER TABLE lot ADD inventory_id INT NOT NULL');
        $this->addSql('ALTER TABLE lot ADD CONSTRAINT FK_B81291B9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id)');
        $this->addSql('CREATE INDEX IDX_B81291B9EEA759 ON lot (inventory_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commercial_sheet DROP FOREIGN KEY FK_A84293C39EEA759');
        $this->addSql('DROP INDEX IDX_A84293C39EEA759 ON commercial_sheet');
        $this->addSql('ALTER TABLE commercial_sheet DROP inventory_id');
        $this->addSql('ALTER TABLE lot DROP FOREIGN KEY FK_B81291B9EEA759');
        $this->addSql('DROP INDEX IDX_B81291B9EEA759 ON lot');
        $this->addSql('ALTER TABLE lot DROP inventory_id');
    }
}
