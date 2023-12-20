<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class MenuItemLinkCrudController extends MenuItemCrudController
{
    public function configureFields(string $pageName): iterable
    {
        [
            'nameField' => $nameField,
            'linkField' => $linkField,
            'parentField' => $parentField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_NEW) {
            return [$nameField, $linkField, $parentField];
        }

        return parent::configureFields($pageName);
    }
}
