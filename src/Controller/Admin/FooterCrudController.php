<?php

declare(strict_types=1);

namespace App\Controller\Admin;

final class FooterCrudController extends AbstractMenuCrudController
{
    public function getPageTitle(): string
    {
        return 'footer.page_title';
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.config_footer';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'menu.config_footer';
    }

    public function getMenuItemCrudControllerClass(): string
    {
        return MenuItemFooterCrudController::class;
    }

    public function getMenuItemsIndex(): int
    {
        return DashboardController::MENU_INDEX[MenuItemFooterCrudController::class];
    }
}
