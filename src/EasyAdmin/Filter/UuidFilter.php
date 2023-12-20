<?php

declare(strict_types=1);

namespace App\EasyAdmin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Simple and generic uuid filter for EA.
 *
 * @example return $filters
 *              ->add(UuidFilter::new('id', TextType::class))
 */
final class UuidFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextType::class);
    }

    /**
     * Apply an exact match filter for the uuid. Doctrine can handle the value as
     * a string and we don't have to convert it in a uuid object.
     */
    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $queryBuilder->andWhere(sprintf('%s.%s = :value', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty()))
            ->setParameter('value', $filterDataDto->getValue());
    }
}
