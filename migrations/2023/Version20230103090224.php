<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230103090224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dummy migration to test the workflow';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT 1 as id');
    }

    public function down(Schema $schema): void
    {
    }
}
