<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

/**
 * Specific page for services.
 */
final class ServiceCrudController extends AbstractProductCrudController
{
    public function getProductType(): ProductType
    {
        return ProductType::SERVICE;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'menu.services';
    }

    public function getEntityLabelInSingular(): string
    {
        return 'SERVICE';
    }

    public function createEntity(string $entityFqcn): Product
    {
        $product = parent::createEntity($entityFqcn);
        $product->setCurrency(null); // remove the default value which is not needed here

        return $product;
    }

    public function configureFields(string $pageName): iterable
    {
        $panels = $this->getPanels();

        [
            'idField' => $idField,
            'typeField' => $typeField,
            'statusField' => $statusField,
            'visibilityField' => $visibilityField,
            'ownerField' => $ownerField,
            'categoryField' => $categoryField,
            'nameField' => $nameField,
            'descriptionField' => $descriptionField,
            'durationField' => $durationField,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
        ] = $this->getFields($pageName);

        // list
        if ($pageName === Crud::PAGE_INDEX) {
            return [$nameField, $ownerField, $categoryField, $statusField, $visibilityField, $createdAt];
        }

        // forms
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            /** @var ChoiceField $statusField */
            $statusField->setChoices(ProductStatus::cases());
            /** @var ChoiceField $visibilityField */
            $visibilityField->setChoices(ProductVisibility::cases());

            return [$nameField, $ownerField, $categoryField, $statusField, $visibilityField, $descriptionField, $durationField];
        }

        // detail

        return [
            $panels['information'],
            $ownerField,
            $categoryField,
            $statusField,
            $visibilityField,
            $nameField,
            $descriptionField,
            $durationField,

            $panels['tech_information'],
            $idField,
            $typeField,
            $createdAt,
            $updatedAt,
        ];
    }
}
