<?php

declare(strict_types=1);

namespace App\EasyAdmin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

/**
 * Generic enum filter for EA.
 */
final class EnumFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $formType, string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType($formType);
    }

    /**
     * Applie an exact match filter for an enum value.
     */
    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $queryBuilder->andWhere(sprintf('%s.%s = :value', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty()))
            ->setParameter('value', $filterDataDto->getValue());
    }
}
