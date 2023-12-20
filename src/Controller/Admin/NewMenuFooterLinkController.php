<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class NewMenuFooterLinkController extends MenuItemFooterCrudController
{
    public function configureFields(string $pageName): iterable
    {
        [
            'nameField' => $nameField,
            'linkField' => $linkField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_NEW) {
            return [$nameField, $linkField];
        }

        return parent::configureFields($pageName);
    }
}
