<?php

declare(strict_types=1);

namespace App\EasyAdmin\Filter\User;

use App\EasyAdmin\Form\Type\UserType;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

/**
 * Restrict the visible users in the member page.
 */
final class MyUsersFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(UserType::class)
        ;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        /** @var User $user */
        $user = $filterDataDto->getValue();
        $queryBuilder
            ->andWhere('entity.user = :user')
            ->setParameter(':user', $user)
        ;
    }
}
