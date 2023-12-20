<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Menu;

class MenuItemCrudController extends AbstractMenuItemCrudController
{
    public function getEntityLabelInPlural(): string
    {
        return 'menu_items.title';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'menu_item.entitylabelinsingular';
    }

    public function getCode(): string
    {
        return Menu::MENU;
    }

    public function getMenuControllerClass(): string
    {
        return MenuCrudController::class;
    }

    public function getMenuItemsControllerClass(): string
    {
        return __CLASS__;
    }

    public function getNewMenuItemLinkController(): string
    {
        return MenuItemLinkCrudController::class;
    }

    public function getNewMenuItemIconController(): string
    {
        return MenuItemSocialNetworkCrudController::class;
    }
}
