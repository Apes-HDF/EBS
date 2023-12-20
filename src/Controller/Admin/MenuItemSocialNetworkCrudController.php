<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MenuItem;
use App\Enum\Menu\LinkType;
use App\Enum\SocialMediaType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class MenuItemSocialNetworkCrudController extends MenuItemCrudController
{
    public function createEntity(string $entityFqcn): MenuItem
    {
        $menuItem = parent::createEntity($entityFqcn);
        $menuItem->setLinkType(LinkType::SOCIAL_NETWORK);

        return $menuItem;
    }

    public function configureFields(string $pageName): iterable
    {
        [
            'socialMediaTypeField' => $socialMediaTypeField,
            'linkField' => $linkField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_NEW) {
            /** @var ChoiceField $socialMediaTypeField */
            $socialMediaTypeField->setChoices(SocialMediaType::cases());

            return [$socialMediaTypeField, $linkField];
        }

        return parent::configureFields($pageName);
    }
}
