<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201005091201 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_item_commercial_sheet DROP FOREIGN KEY FK_A52BFC70E415FB15');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE order_item_commercial_sheet');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, quantity DOUBLE PRECISION NOT NULL, INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE order_item_commercial_sheet (order_item_id INT NOT NULL, commercial_sheet_id INT NOT NULL, INDEX IDX_A52BFC70371614E4 (commercial_sheet_id), INDEX IDX_A52BFC70E415FB15 (order_item_id), PRIMARY KEY(order_item_id, commercial_sheet_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_item_commercial_sheet ADD CONSTRAINT FK_A52BFC70371614E4 FOREIGN KEY (commercial_sheet_id) REFERENCES commercial_sheet (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_item_commercial_sheet ADD CONSTRAINT FK_A52BFC70E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
