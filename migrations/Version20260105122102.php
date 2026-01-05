<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105122102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_log CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX UNIQ_SAVED_OFFER_USER_JOB ON saved_offer');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SAVED_OFFER_USER_JOB ON saved_offer (user_id, job_offer_id)');
        $this->addSql('ALTER TABLE admin_log CHANGE data data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles JSON NOT NULL');
    }
}
