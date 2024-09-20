<?php

declare(strict_types=1);

namespace App\Tests;

final class TestReference
{
    // common
    public const UUID_404 = '1ed7a2a8-0a77-6dbc-a404-040404040404'; // valid UUID for 404 pages
    public const VALIDATION_ERROR_BLANK = 'This value should not be blank';
    public const VALIDATION_ERROR_ALREADY_USED = 'This value is already used';

    // EasyAdmin
    public const ADMIN_URL = '/admin?crudAction=%s&crudControllerFqcn=%s';
    public const ADMIN_URL_CUSTOM_CONTROLLER = '/admin?routeName=%s';
    public const ACTION_SAVE_AND_RETURN = 'action.create'; // label to create a new item
    public const ACTION_SAVE = 'action.save'; // label to save an existing item

    // address
    public const ADDRESSES_COUNT = 5;

    // configuration
    public const CONFIGURATION_COUNT = 1;

    // groups
    public const GROUP_COUNT = 10;
    public const GROUP_1 = '1ed4bcca-336e-6732-a08c-a15bb85fa24a'; // public - charged
    public const GROUP_1_MEMBER_COUNT = 6;
    public const GROUP_7 = '1ed658d5-8f6c-663a-9bf0-9154f3e29146'; // public - charged
    public const GROUP_5 = '1edc897c-6113-6878-833e-856b0722c68f'; // public - free
    public const GROUP_PRIVATE = '1ed4bcf1-264e-6aa8-8897-3dc24d8aa063'; // private - free

    // users
    public const USER_COUNT = 18;
    public const PASSWORD_FIXTURES = 'apesebs';
    public const PASSWORD = '12345678';
    public const USER_17_EMAIL = 'user17@example.com';

    // user groups
    public const USER_GROUP_COUNT = 13;

    public const USER_GROUP_LOIC_GROUP_7 = '1edb8e0d-bdeb-6fac-9be9-811296e7064d';

    public const GROUP_OFFER_GROUP_1_1 = '1edc7128-0bd1-69f8-9c49-5362a3c8c763';
    public const GROUP_OFFER_COUNT = 17;

    public const ADMIN_CAMILLE = '1ed674d3-886f-6660-bcf3-15f868662c0c';

    public const ADMIN_LOIC = '1ed674d3-886f-6ad4-bc25-15f868662c0c';
    public const ADMIN_SARAH = '1ed7a201-b796-69d2-a508-59c49e0f28ee';
    public const ADMIN_KEVIN = '1ed69804-eeb9-6c32-990b-632c3a6846ba';
    public const PLACE_7 = '1ed69ae4-305f-68a8-b35a-f5221b39ed0c';
    public const PLACE_APES = '1edd876a-bb8e-66a8-b352-692256fc3f7e';
    public const USER_17 = '1ed69ae4-305f-6cfe-aee4-f5221b39ed0c';
    public const USER_16 = '1ed8d15a-fa38-6f54-9188-f15f4a44bdf2';
    public const USER_11 = '1ed6ce0e-8685-6cf2-93b7-af7ed3e58b6f';

    public const USER_12_CONFIRMATION_TOKEN = '3PpTWgYdgNZcuRTbqZTS5HRihEGGhw5rCszuo7XYAPJ9dEwttR';
    public const USER_13_CONFIRMATION_TOKEN = 'DrCaEPr3pKM9e8PkfUZiZZsAe5nwcgBDpQjKbuaJ3ukzL5qLv9';
    public const USER_14_LOST_PASSWORD_TOKEN = 'cuYxfS5eCWX2FYtJwWdhHZrGY6W1KT7UBV6CeARK2E2s4V3SKB';
    public const USER_14_CONFIRMATION_TOKEN = self::USER_14_LOST_PASSWORD_TOKEN; // let's use the same
    public const USER_15_LOST_PASSWORD_TOKEN = 'A4QJZqhf3wFnoJCf65xLwce2f7aMWkLEoZHshvHCDWC61vQSAv';

    public const USER_EMAIL = 'user17@example.com';
    public const ADMIN_EMAIL = 'loic@example.com';

    // products
    public const PRODUCTS_COUNT = 18;
    public const PRODUCTS_NOT_INDEXABLE_COUNT = 1;
    public const PRODUCTS_INDEXABLE_COUNT = self::PRODUCTS_COUNT - self::PRODUCTS_NOT_INDEXABLE_COUNT;
    public const PRODUCTS_RESTRICTED_COUNT = 5;
    public const PRODUCTS_VISIBLE_COUNT = 12;
    public const PRODUCT_AVAILABILITIES_COUNT = 4;

    public const USER_17_SERVICES_COUNT = 0;
    public const USER_8_SERVICES_COUNT = 1;

    public const USER_8_OBJECTS_COUNT = 2;
    public const ADMIN_LOIC_OBJECTS_COUNT = 3;

    public const SARAH_SERVICES_COUNT = 2;

    public const OBJECT_LOIC_1 = '1ed7a2a8-0a77-6dbc-a34f-f3a729006754';
    public const OBJECT_LOIC_2 = '1ed9e294-7b0b-63f2-984c-61feb91f1a99';
    public const OBJECT_LOIC_PHOTO_1 = '4437be7d-ce40-43f0-99b4-4adddcc3316f.jpg"';
    public const OBJECT_USER_16_1 = '1edae186-1b1e-6da8-8b71-e114a7d26c2e';
    public const SERVICE_USER_16_1 = '1edae1d3-f66a-6f68-8057-41b63a425612';
    public const SERVICE_LOIC_1 = '1ed7a2a8-0a78-605a-a8e0-f3a729006754';
    public const OBJECT_KEVIN_1 = '1edc4bdc-c352-64fe-960a-a90b81c8da31';
    public const OBJECT_PLACE_6 = '1edf938b-7344-6684-87d5-d36fc869cf92';

    // product availabilities
    public const OBJECT_LOIC_1_AVAILABILITY_1 = '1edc2761-1291-690c-9d72-eb08fd92218b';

    // categories
    public const CATEGORIES_COUNT = 27;
    public const CATEGORY_OBJECT_1 = '1ed7b92a-cc6b-6eda-a656-07f15205faab'; // Activités manuelles et créatives
    public const CATEGORY_OBJECT_2 = '1edbea38-a8fe-6568-8e68-117f36d0bc19'; // Vélos & accessoires
    public const CATEGORY_SERVICE_1 = '1ed7b9af-b515-6dbe-a2e8-1338dbc1e4f2'; // Aide bricolage

    public const SUB_CATEGORY_SERVICE_1 = '1edceee2-8b52-603c-95c8-1b2191c003a1'; // Cours de musique

    // menu and footer
    public const MENU_COUNT = 2;
    public const MENU_ITEMS_COUNT = 15;
    public const MENU_HEADER_ITEM_FIRST = '58a72426-57e4-4251-9c32-d29603bdcf5b';
    public const MENU_HEADER_ITEM_LAST = 'ac678c07-421f-4968-b2f8-74c9f2f22fcf';

    public const MENU_FOOTER_ITEM_FIRST = '1ed9717c-26af-622e-b50d-c14b0dba3b13';
    public const MENU_FOOTER_ITEM_LAST = '82dc7e49-8db8-46f5-b7db-9e8a8bea1b5a';

    // pages
    public const PAGE_1 = '1ed92636-39fe-651e-821f-91f56e56ac44';

    // service request
    public const SERVICE_REQUEST_COUNT = 6;
    public const SERVICE_REQUEST_1 = '1ed9b3b1-26cd-68a6-8709-cbb2b8939b2e';
    public const SERVICE_REQUEST_2 = '1edb1f55-4270-6f8e-bc16-8bcfef1b7183';
    public const SERVICE_REQUEST_3 = '1edb2975-4ce1-644e-8167-efa11e476fb6';
    public const SERVICE_REQUEST_4 = '1edb68e7-fe43-6bc2-86fb-a553ef7039c9';

    public const SERVICE_REQUEST_WORKFLOW_FLASH_SUCCESS = 'app.controller.user.service_request.service_request_status_workflow_controller.flash';

    public const SERVICE_REQUEST_BASE_ROUTE = '/fr/mon-compte/service/';

    // messages
    public const MESSAGES_COUNT = 2;

    // payments
    final public const PAYMENT_USER_16_1 = '1edcefc9-45b3-6a3e-b4a6-db137f56da56';

    // platform offer
    final public const PLATFORM_OFFER_1 = '016b2a27-1037-6d47-bcdc-ec5efbd723f2';
}
