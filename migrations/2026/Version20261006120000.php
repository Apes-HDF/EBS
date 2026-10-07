<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Security: invalidate all pending lost password and confirmation tokens.
 */
final class Version20261006120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Invalidate pending lost password and confirmation tokens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE "user" SET lost_password_token = NULL, lost_password_expires_at = NULL WHERE lost_password_token IS NOT NULL');
        $this->addSql('UPDATE "user" SET confirmation_token = NULL, confirmation_expires_at = NULL WHERE confirmation_token IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // irreversible: tokens can't be restored
    }
}
