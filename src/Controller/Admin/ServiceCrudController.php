<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Enum\Group\UserMembership;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

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
        $product->setVisibility(ProductVisibility::RESTRICTED);

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
            'groupsField' => $groupsField,
            'groupsFieldList' => $groupsFieldList,
            'ownerField' => $ownerField,
            'categoryField' => $categoryField,
            'nameField' => $nameField,
            'descriptionField' => $descriptionField,
            'durationField' => $durationField,
            'imageField' => $imageField,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
        ] = $this->getFields($pageName);

        // list
        if ($pageName === Crud::PAGE_INDEX) {
            return [$nameField, $ownerField, $categoryField, $statusField, $visibilityField, $groupsFieldList, $imageField, $createdAt];
        }

        /** @var ImageField $imageField */
        $imageField->setCustomOption('first_image_only', false);

        // forms
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            /** @var ChoiceField $statusField */
            $statusField->setChoices(ProductStatus::cases());

            if ($pageName === Crud::PAGE_NEW) {
                return [$nameField, $ownerField, $categoryField, $statusField, $groupsField, $descriptionField, $imageField, $durationField];
            }
            /** @var Product|null $product */
            $product = $this->getContext()?->getEntity()?->getInstance();
            $owner = $product?->getOwner();
            if (null !== $owner && !$owner->getUserGroupsConfirmedWithServices()->isEmpty()) {
                /** @var AssociationField $groupsField */
                $groupsField->setQueryBuilder(function (QueryBuilder $queryBuilder) use ($owner) {
                    return $queryBuilder
                        ->join('entity.userGroups', 'ug')
                        ->andWhere('ug.membership != :membership')
                        ->andWhere('ug.user = :user')
                        ->andWhere('entity.servicesEnabled = :true')
                        ->setParameter('user', $owner)
                        ->setParameter('membership', UserMembership::INVITATION)
                        ->setParameter('true', true)
                    ;
                });
            } else {
                $i18prefix = $this->getI18nPrefix(self::class);
                /** @var AssociationField $groupsField */
                $groupsField->setHelp($i18prefix.'.field.groups.help')->setDisabled();
            }

            return [$nameField, $ownerField, $categoryField, $statusField, $groupsField, $descriptionField, $imageField, $durationField];
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
            $imageField,
            $panels['tech_information'],
            $idField,
            $typeField,
            $createdAt,
            $updatedAt,
        ];
    }
}
