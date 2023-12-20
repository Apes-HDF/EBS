<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

/**
 * Specific page for objects.
 */
final class ObjectCrudController extends AbstractProductCrudController
{
    public function getProductType(): ProductType
    {
        return ProductType::OBJECT;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.objects';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'OBJECT';
    }

    public function configureFields(string $pageName): iterable
    {
        $panels = $this->getPanels();

        [
            'idField' => $idField,
            'typeField' => $typeField,
            'statusField' => $statusField,
            'visibilityField' => $visibilityField,
            'groupsField' => $groupsField,
            'ownerField' => $ownerField,
            'categoryField' => $categoryField,
            'nameField' => $nameField,
            'descriptionField' => $descriptionField,
            'ageField' => $ageField,
            'depositField' => $depositField,
            'currencyField' => $currencyField,
            'imageField' => $imageField,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'addressField' => $addressField,
            'preferredLoanDuration' => $preferredLoanDuration,
        ] = $this->getFields($pageName);

        // list
        if ($pageName === Crud::PAGE_INDEX) {
            return [$nameField, $ownerField, $categoryField, $statusField, $visibilityField, $imageField, $createdAt];
        }

        /** @var ImageField $imageField */
        $imageField->setCustomOption('first_image_only', false);

        // forms
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            /** @var ChoiceField $statusField */
            $statusField->setChoices(ProductStatus::cases());
            /** @var ChoiceField $visibilityField */
            $visibilityField->setChoices(ProductVisibility::cases());

            return [$nameField, $ownerField, $categoryField, $statusField, $visibilityField, $descriptionField, $ageField, $depositField, $currencyField, $imageField, $preferredLoanDuration];
        }

        // detail

        return [
            $panels['information'],
            $ownerField,
            $categoryField,
            $statusField,
            $visibilityField,
            $groupsField,
            $nameField,
            $descriptionField,
            $ageField,
            $depositField,
            $currencyField,
            $imageField,
            $addressField,
            $preferredLoanDuration,

            $panels['tech_information'],
            $idField,
            $typeField,
            $createdAt,
            $updatedAt, ];
    }
}
