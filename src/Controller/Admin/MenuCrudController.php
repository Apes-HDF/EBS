<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\FieldTrait;

final class MenuCrudController extends AbstractMenuCrudController
{
    use FieldTrait;

    public function getPageTitle(): string
    {
        return 'menu.page_title';
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.config_menu';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'menu.config_menu';
    }

    public function getMenuItemCrudControllerClass(): string
    {
        return MenuItemCrudController::class;
    }

    public function getMenuItemsIndex(): int
    {
        return DashboardController::MENU_INDEX[MenuItemCrudController::class];
    }
}
