<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to fix schema: JSON types, DATETIME types, nullable columns, and indexes
 */
final class Version20251123162734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix schema: JSON types with DC2Type comments, DATETIME_IMMUTABLE types, and nullable columns';
    }

    public function up(Schema $schema): void
    {
        // Fix user.roles - add JSON type with DC2Type comment
        $this->addSql('ALTER TABLE `user` MODIFY roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');

        // Fix admin_log.data - add JSON type with DC2Type comment
        $this->addSql('ALTER TABLE admin_log MODIFY data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');

        // Ensure application.cv_filename is nullable
        $this->addSql('ALTER TABLE application MODIFY cv_filename VARCHAR(255) DEFAULT NULL');

        // Ensure company columns are nullable where needed
        $this->addSql('ALTER TABLE company MODIFY logo_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company MODIFY website VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company MODIFY location VARCHAR(255) DEFAULT NULL');

        // Ensure job_offer.location is nullable
        $this->addSql('ALTER TABLE job_offer MODIFY location VARCHAR(255) DEFAULT NULL');

        // Fix messenger_messages.delivered_at - DATETIME NULL with DC2Type comment
        $this->addSql('ALTER TABLE messenger_messages MODIFY delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');

        // Ensure user columns are properly typed
        $this->addSql('ALTER TABLE `user` MODIFY location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` MODIFY cv_filename VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert user.roles
        $this->addSql('ALTER TABLE `user` MODIFY roles JSON NOT NULL');

        // Revert admin_log.data
        $this->addSql('ALTER TABLE admin_log MODIFY data JSON DEFAULT NULL');

        // Revert application.cv_filename
        $this->addSql('ALTER TABLE application MODIFY cv_filename VARCHAR(255) DEFAULT NULL');

        // Revert company columns
        $this->addSql('ALTER TABLE company MODIFY logo_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company MODIFY website VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company MODIFY location VARCHAR(255) DEFAULT NULL');

        // Revert job_offer.location
        $this->addSql('ALTER TABLE job_offer MODIFY location VARCHAR(255) DEFAULT NULL');

        // Revert messenger_messages.delivered_at
        $this->addSql('ALTER TABLE messenger_messages MODIFY delivered_at DATETIME DEFAULT NULL');

        // Revert user columns
        $this->addSql('ALTER TABLE `user` MODIFY location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` MODIFY cv_filename VARCHAR(255) DEFAULT NULL');
    }
}
