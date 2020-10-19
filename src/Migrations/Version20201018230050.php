<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201018230050 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commercial_sheet_item_lot (id INT AUTO_INCREMENT NOT NULL, commercial_sheet_item_id INT NOT NULL, lot_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_C4CA6C054EE9E066 (commercial_sheet_item_id), INDEX IDX_C4CA6C05A8CBA5F7 (lot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commercial_sheet_item_lot ADD CONSTRAINT FK_C4CA6C054EE9E066 FOREIGN KEY (commercial_sheet_item_id) REFERENCES commercial_sheet_item (id)');
        $this->addSql('ALTER TABLE commercial_sheet_item_lot ADD CONSTRAINT FK_C4CA6C05A8CBA5F7 FOREIGN KEY (lot_id) REFERENCES lot (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commercial_sheet_item_lot');
    }
}
