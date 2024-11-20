<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241106085124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(191) NOT NULL, executed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, execution_time INT DEFAULT NULL, PRIMARY KEY(version))');
        $this->addSql('CREATE TABLE platform_offer (id UUID NOT NULL, configuration_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, price INT NOT NULL, currency VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B8AE3BA73F32DD8 ON platform_offer (configuration_id)');
        $this->addSql('CREATE INDEX IDX_1B8AE3BA8CDE5729 ON platform_offer (type)');
        $this->addSql('COMMENT ON COLUMN platform_offer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_offer.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN platform_offer.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE platform_offer ADD CONSTRAINT FK_1B8AE3BA73F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group" ADD services_enabled BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD platform_offer_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD membership_paid BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD start_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD payed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".platform_offer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".end_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".payed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649D6B674FC FOREIGN KEY (platform_offer_id) REFERENCES platform_offer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649D6B674FC ON "user" (platform_offer_id)');
        $this->addSql('ALTER TABLE messenger_messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER available_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER delivered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649D6B674FC');
        $this->addSql('ALTER TABLE platform_offer DROP CONSTRAINT FK_1B8AE3BA73F32DD8');
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('DROP TABLE platform_offer');
        $this->addSql('DROP INDEX IDX_8D93D649D6B674FC');
        $this->addSql('ALTER TABLE "user" DROP platform_offer_id');
        $this->addSql('ALTER TABLE "user" DROP membership_paid');
        $this->addSql('ALTER TABLE "user" DROP start_at');
        $this->addSql('ALTER TABLE "user" DROP end_at');
        $this->addSql('ALTER TABLE "user" DROP payed_at');
        $this->addSql('ALTER TABLE messenger_messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER available_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE messenger_messages ALTER delivered_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS NULL');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS NULL');
        $this->addSql('ALTER TABLE "group" DROP services_enabled');
    }
}
