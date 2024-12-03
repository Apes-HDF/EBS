<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126103656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE menu_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE address (id UUID NOT NULL, address VARCHAR(255) NOT NULL, address_supplement VARCHAR(255) DEFAULT NULL, display_name VARCHAR(255) DEFAULT NULL, street_number VARCHAR(20) NOT NULL, street_name VARCHAR(255) NOT NULL, sub_locality VARCHAR(255) DEFAULT NULL, locality VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, country VARCHAR(2) NOT NULL, latitude NUMERIC(11, 7) DEFAULT NULL, longitude NUMERIC(11, 7) DEFAULT NULL, provided_by VARCHAR(20) DEFAULT NULL, attribution VARCHAR(255) DEFAULT NULL, osm_type VARCHAR(20) DEFAULT NULL, osm_id BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN address.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN address.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN address.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE category (id UUID NOT NULL, parent_id UUID DEFAULT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)');
        $this->addSql('CREATE INDEX IDX_64C19C18CDE5729 ON category (type)');
        $this->addSql('COMMENT ON COLUMN category.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN category.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN category.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN category.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE configuration (id INT NOT NULL, type VARCHAR(255) NOT NULL, configuration JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN configuration.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN configuration.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "group" (id UUID NOT NULL, parent_id UUID DEFAULT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, membership VARCHAR(255) NOT NULL, invitation_by_admin BOOLEAN NOT NULL, services_enabled BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C5989D9B62 ON "group" (slug)');
        $this->addSql('CREATE INDEX IDX_6DC044C5727ACA70 ON "group" (parent_id)');
        $this->addSql('CREATE INDEX IDX_6DC044C58CDE5729 ON "group" (type)');
        $this->addSql('COMMENT ON COLUMN "group".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "group".parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "group".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "group".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE group_offer (id UUID NOT NULL, group_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, price INT NOT NULL, currency VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_79FAAC59FE54D947 ON group_offer (group_id)');
        $this->addSql('CREATE INDEX IDX_79FAAC598CDE5729 ON group_offer (type)');
        $this->addSql('COMMENT ON COLUMN group_offer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN group_offer.group_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN group_offer.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN group_offer.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE menu (id INT NOT NULL, logo VARCHAR(255) DEFAULT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D053A9377153098 ON menu (code)');
        $this->addSql('COMMENT ON COLUMN menu.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN menu.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE menu_item (id UUID NOT NULL, parent_id UUID DEFAULT NULL, menu_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, linkType VARCHAR(255) NOT NULL, mediaType VARCHAR(255) DEFAULT NULL, link VARCHAR(255) NOT NULL, position INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D754D550727ACA70 ON menu_item (parent_id)');
        $this->addSql('CREATE INDEX IDX_D754D550CCD7E912 ON menu_item (menu_id)');
        $this->addSql('COMMENT ON COLUMN menu_item.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN menu_item.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE message (id UUID NOT NULL, service_request_id UUID NOT NULL, type VARCHAR(255) NOT NULL, message_template VARCHAR(255) DEFAULT NULL, message_parameters JSON NOT NULL, message TEXT NOT NULL, owner_read BOOLEAN NOT NULL, owner_read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, recipient_read BOOLEAN NOT NULL, recipient_read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307FD42F8111 ON message (service_request_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FD42F81118B8E8428 ON message (service_request_id, created_at)');
        $this->addSql('COMMENT ON COLUMN message.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message.service_request_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message.owner_read_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN message.recipient_read_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN message.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE page (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content TEXT NOT NULL, enabled BOOLEAN NOT NULL, home BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
        $this->addSql('CREATE INDEX IDX_140AB620989D9B62 ON page (slug)');
        $this->addSql('COMMENT ON COLUMN page.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN page.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN page.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE payment (id UUID NOT NULL, number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, client_email VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, total_amount INT DEFAULT NULL, currency_code VARCHAR(255) DEFAULT NULL, details JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "user" UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6D28840D356B3608 ON payment ("user")');
        $this->addSql('COMMENT ON COLUMN payment.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN payment.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN payment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN payment."user" IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE payment_token (hash VARCHAR(255) NOT NULL, details TEXT DEFAULT NULL, after_url TEXT DEFAULT NULL, target_url TEXT NOT NULL, gateway_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(hash))');
        $this->addSql('COMMENT ON COLUMN payment_token.details IS \'(DC2Type:object)\'');
        $this->addSql('COMMENT ON COLUMN payment_token.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN payment_token.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE platform_offer (id UUID NOT NULL, configuration_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, price INT NOT NULL, currency VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B8AE3BA73F32DD8 ON platform_offer (configuration_id)');
        $this->addSql('CREATE INDEX IDX_1B8AE3BA8CDE5729 ON platform_offer (type)');
        $this->addSql('COMMENT ON COLUMN platform_offer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN platform_offer.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN platform_offer.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE product (id UUID NOT NULL, category_id UUID NOT NULL, owner_id UUID NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, visibility VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, images JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, age TEXT DEFAULT NULL, deposit INT DEFAULT NULL, currency VARCHAR(255) DEFAULT NULL, preferred_loan_duration VARCHAR(255) DEFAULT NULL, duration TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD7E3C61F9 ON product (owner_id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD8CDE57297B00651C518E43007E3C61F9 ON product (type, status, visibility, owner_id)');
        $this->addSql('COMMENT ON COLUMN product.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE product_group (product_id UUID NOT NULL, group_id UUID NOT NULL, PRIMARY KEY(product_id, group_id))');
        $this->addSql('CREATE INDEX IDX_CC9C3F994584665A ON product_group (product_id)');
        $this->addSql('CREATE INDEX IDX_CC9C3F99FE54D947 ON product_group (group_id)');
        $this->addSql('COMMENT ON COLUMN product_group.product_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_group.group_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE product_availability (id UUID NOT NULL, product_id UUID NOT NULL, service_request_id UUID DEFAULT NULL, type VARCHAR(255) NOT NULL, mode VARCHAR(255) NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B21380D4D42F8111 ON product_availability (service_request_id)');
        $this->addSql('CREATE INDEX IDX_B21380D44584665A ON product_availability (product_id)');
        $this->addSql('COMMENT ON COLUMN product_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_availability.product_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_availability.service_request_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN product_availability.start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product_availability.end_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product_availability.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN product_availability.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE service_request (id UUID NOT NULL, owner_id UUID NOT NULL, product_id UUID NOT NULL, recipient_id UUID NOT NULL, status VARCHAR(255) NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F413DD037E3C61F9 ON service_request (owner_id)');
        $this->addSql('CREATE INDEX IDX_F413DD034584665A ON service_request (product_id)');
        $this->addSql('CREATE INDEX IDX_F413DD03E92F8F78 ON service_request (recipient_id)');
        $this->addSql('CREATE INDEX IDX_F413DD037E3C61F9E92F8F78 ON service_request (owner_id, recipient_id)');
        $this->addSql('COMMENT ON COLUMN service_request.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN service_request.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN service_request.product_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN service_request.recipient_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN service_request.start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN service_request.end_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN service_request.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN service_request.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, category_id UUID DEFAULT NULL, address_id UUID DEFAULT NULL, platform_offer_id UUID DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, email VARCHAR(180) NOT NULL, email_confirmed BOOLEAN NOT NULL, lastname VARCHAR(180) DEFAULT NULL, firstname VARCHAR(180) DEFAULT NULL, name VARCHAR(180) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, main_admin_account BOOLEAN NOT NULL, dev_account BOOLEAN NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, login_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, sms_notifications BOOLEAN DEFAULT NULL, schedule VARCHAR(180) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, vacation_mode BOOLEAN NOT NULL, membership_paid BOOLEAN DEFAULT false NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, payed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, confirmation_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, lost_password_token VARCHAR(255) DEFAULT NULL, lost_password_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE INDEX IDX_8D93D64912469DE2 ON "user" (category_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649F5B7AF75 ON "user" (address_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649D6B674FC ON "user" (platform_offer_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6498CDE5729 ON "user" (type)');
        $this->addSql('CREATE INDEX IDX_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE INDEX IDX_8D93D649C05FB297 ON "user" (confirmation_token)');
        $this->addSql('CREATE INDEX IDX_8D93D649C9817729 ON "user" (lost_password_token)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".address_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".platform_offer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".end_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".payed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".confirmation_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".lost_password_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_group (id UUID NOT NULL, membership VARCHAR(255) NOT NULL, main_admin_account BOOLEAN NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, payed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "user" UUID NOT NULL, "group" UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F02BF9D356B3608 ON user_group ("user")');
        $this->addSql('CREATE INDEX IDX_8F02BF9D988D0E7A ON user_group ("group")');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F02BF9D8D93D6496DC044C5 ON user_group ("user", "group")');
        $this->addSql('COMMENT ON COLUMN user_group.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_group.start_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_group.end_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_group.payed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_group.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_group.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_group."user" IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_group."group" IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group" ADD CONSTRAINT FK_6DC044C5727ACA70 FOREIGN KEY (parent_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_offer ADD CONSTRAINT FK_79FAAC59FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FD42F8111 FOREIGN KEY (service_request_id) REFERENCES service_request (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D356B3608 FOREIGN KEY ("user") REFERENCES "user" ("id") NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE platform_offer ADD CONSTRAINT FK_1B8AE3BA73F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_group ADD CONSTRAINT FK_CC9C3F994584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_group ADD CONSTRAINT FK_CC9C3F99FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_availability ADD CONSTRAINT FK_B21380D44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_availability ADD CONSTRAINT FK_B21380D4D42F8111 FOREIGN KEY (service_request_id) REFERENCES service_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_request ADD CONSTRAINT FK_F413DD037E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_request ADD CONSTRAINT FK_F413DD034584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_request ADD CONSTRAINT FK_F413DD03E92F8F78 FOREIGN KEY (recipient_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64912469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649D6B674FC FOREIGN KEY (platform_offer_id) REFERENCES platform_offer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9D356B3608 FOREIGN KEY ("user") REFERENCES "user" ("id") NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9D988D0E7A FOREIGN KEY ("group") REFERENCES "group" ("id") NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE configuration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE menu_id_seq CASCADE');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE "group" DROP CONSTRAINT FK_6DC044C5727ACA70');
        $this->addSql('ALTER TABLE group_offer DROP CONSTRAINT FK_79FAAC59FE54D947');
        $this->addSql('ALTER TABLE menu_item DROP CONSTRAINT FK_D754D550727ACA70');
        $this->addSql('ALTER TABLE menu_item DROP CONSTRAINT FK_D754D550CCD7E912');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FD42F8111');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D356B3608');
        $this->addSql('ALTER TABLE platform_offer DROP CONSTRAINT FK_1B8AE3BA73F32DD8');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD7E3C61F9');
        $this->addSql('ALTER TABLE product_group DROP CONSTRAINT FK_CC9C3F994584665A');
        $this->addSql('ALTER TABLE product_group DROP CONSTRAINT FK_CC9C3F99FE54D947');
        $this->addSql('ALTER TABLE product_availability DROP CONSTRAINT FK_B21380D44584665A');
        $this->addSql('ALTER TABLE product_availability DROP CONSTRAINT FK_B21380D4D42F8111');
        $this->addSql('ALTER TABLE service_request DROP CONSTRAINT FK_F413DD037E3C61F9');
        $this->addSql('ALTER TABLE service_request DROP CONSTRAINT FK_F413DD034584665A');
        $this->addSql('ALTER TABLE service_request DROP CONSTRAINT FK_F413DD03E92F8F78');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64912469DE2');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649F5B7AF75');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649D6B674FC');
        $this->addSql('ALTER TABLE user_group DROP CONSTRAINT FK_8F02BF9D356B3608');
        $this->addSql('ALTER TABLE user_group DROP CONSTRAINT FK_8F02BF9D988D0E7A');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE group_offer');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_item');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_token');
        $this->addSql('DROP TABLE platform_offer');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_group');
        $this->addSql('DROP TABLE product_availability');
        $this->addSql('DROP TABLE service_request');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
