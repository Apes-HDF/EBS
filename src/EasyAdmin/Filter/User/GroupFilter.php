<?php

declare(strict_types=1);

namespace App\EasyAdmin\Filter\User;

use App\EasyAdmin\Form\Type\GroupType;
use App\Entity\Group;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

/**
 * Filter on a given group in the members page.
 */
final class GroupFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(GroupType::class);
    }

    /**
     * The join is done on userGroups.
     */
    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        /** @var Group $group */
        $group = $filterDataDto->getValue();
        $queryBuilder
            ->innerJoin(\sprintf('%s.userGroups', $filterDataDto->getEntityAlias()), 'ug')
            ->andWhere('ug.group = :group')
            ->setParameter(':group', $group->getId())
        ;
    }
}
