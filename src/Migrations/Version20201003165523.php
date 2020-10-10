<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201003165523 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE commercial_sheet_item (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, pu DOUBLE PRECISION NOT NULL, quantity INT NOT NULL, designation VARCHAR(255) NOT NULL, reference VARCHAR(255) DEFAULT NULL, INDEX IDX_56BEE60B4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commercial_sheet_item_commercial_sheet (commercial_sheet_item_id INT NOT NULL, commercial_sheet_id INT NOT NULL, INDEX IDX_32BE32CE4EE9E066 (commercial_sheet_item_id), INDEX IDX_32BE32CE371614E4 (commercial_sheet_id), PRIMARY KEY(commercial_sheet_item_id, commercial_sheet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commercial_sheet_item ADD CONSTRAINT FK_56BEE60B4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE commercial_sheet_item_commercial_sheet ADD CONSTRAINT FK_32BE32CE4EE9E066 FOREIGN KEY (commercial_sheet_item_id) REFERENCES commercial_sheet_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commercial_sheet_item_commercial_sheet ADD CONSTRAINT FK_32BE32CE371614E4 FOREIGN KEY (commercial_sheet_id) REFERENCES commercial_sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enterprise ADD tva DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commercial_sheet_item_commercial_sheet DROP FOREIGN KEY FK_32BE32CE4EE9E066');
        $this->addSql('DROP TABLE commercial_sheet_item');
        $this->addSql('DROP TABLE commercial_sheet_item_commercial_sheet');
        $this->addSql('ALTER TABLE enterprise DROP tva');
    }
}
