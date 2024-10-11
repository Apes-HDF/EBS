<?php

declare(strict_types=1);

namespace App\EasyAdmin\Filter\UserGroup;

use App\EasyAdmin\Form\Type\GroupType;
use App\Entity\Group;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

/**
 * Restrict the visible groups in the member page.
 */
final class MyGroupFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(GroupType::class)
        ;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        /** @var Group $group */
        $group = $filterDataDto->getValue();
        $queryBuilder
            ->andWhere('entity.group = :group')
            ->setParameter(':group', $group->getId())
        ;
    }
}
