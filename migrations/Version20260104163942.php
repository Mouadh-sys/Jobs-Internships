<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104163942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on saved_offer (user_id, job_offer_id)';
    }

    public function up(Schema $schema): void
    {
        // Add unique constraint to prevent duplicate saved offers
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SAVED_OFFER_USER_JOB ON saved_offer (user_id, job_offer_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove unique constraint
        $this->addSql('DROP INDEX UNIQ_SAVED_OFFER_USER_JOB ON saved_offer');
    }
}
