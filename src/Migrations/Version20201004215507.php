<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201004215507 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE enterprise ADD subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enterprise ADD CONSTRAINT FK_B1B36A039A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('CREATE INDEX IDX_B1B36A039A1887DC ON enterprise (subscription_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE enterprise DROP FOREIGN KEY FK_B1B36A039A1887DC');
        $this->addSql('DROP INDEX IDX_B1B36A039A1887DC ON enterprise');
        $this->addSql('ALTER TABLE enterprise DROP subscription_id');
    }
}
