<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190426070905 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15E7877946');
        $this->addSql('DROP INDEX IDX_EA351E15E7877946 ON contribution');
        $this->addSql('ALTER TABLE contribution CHANGE coordinator_id approved_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E152D234F6A FOREIGN KEY (approved_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_EA351E152D234F6A ON contribution (approved_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E152D234F6A');
        $this->addSql('DROP INDEX IDX_EA351E152D234F6A ON contribution');
        $this->addSql('ALTER TABLE contribution CHANGE approved_by_id coordinator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15E7877946 FOREIGN KEY (coordinator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_EA351E15E7877946 ON contribution (coordinator_id)');
    }
}
