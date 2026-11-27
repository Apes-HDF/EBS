<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change of email: the new email is stored as pending until it is confirmed.
 */
final class Version20261030085302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the pending email fields to the user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD pending_email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD pending_email_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD pending_email_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".pending_email_expires_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP pending_email');
        $this->addSql('ALTER TABLE "user" DROP pending_email_token');
        $this->addSql('ALTER TABLE "user" DROP pending_email_expires_at');
    }
}
