<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201220161146 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock_movement CHANGE lot_id lot_id INT DEFAULT NULL, CHANGE product_id inventory_availability_id INT NOT NULL');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B513CA50D8 FOREIGN KEY (inventory_availability_id) REFERENCES inventory_availability (id)');
        $this->addSql('CREATE INDEX IDX_BB1BC1B513CA50D8 ON stock_movement (inventory_availability_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B513CA50D8');
        $this->addSql('DROP INDEX IDX_BB1BC1B513CA50D8 ON stock_movement');
        $this->addSql('ALTER TABLE stock_movement CHANGE lot_id lot_id INT NOT NULL, CHANGE inventory_availability_id product_id INT NOT NULL');
    }
}
