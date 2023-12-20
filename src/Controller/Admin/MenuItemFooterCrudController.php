<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Menu;

class MenuItemFooterCrudController extends AbstractMenuItemCrudController
{
    public function getEntityLabelInPlural(): string
    {
        return 'footer_items.title';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'menu_item.entitylabelinsingular';
    }

    public function getCode(): string
    {
        return Menu::FOOTER;
    }

    public function getMenuControllerClass(): string
    {
        return FooterCrudController::class;
    }

    public function getMenuItemsControllerClass(): string
    {
        return __CLASS__;
    }

    public function getNewMenuItemLinkController(): string
    {
        return NewMenuFooterLinkController::class;
    }

    public function getNewMenuItemIconController(): string
    {
        return MenuItemMenuSocialNetwordFooterCrudController::class;
    }
}
