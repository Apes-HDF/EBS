<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Filter\UserGroup\MyGroupFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\EasyAdmin\Form\Type\OfferTypeType;
use App\Entity\GroupOffer;
use App\Entity\User;
use App\Enum\Group\GroupMembership;
use App\Enum\Group\UserMembership;
use App\Enum\OfferType;
use App\Security\Checker\AuthorizationChecker;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

/**
 * @see GroupOfferCrudControllerTest
 * @see GroupOfferCrudControllerAsGroupAdminTest
 */
final class GroupOfferCrudController extends AbstractCrudController implements GroupAdminSecuredCrudControllerInterface
{
    use FlashTrait;
    use FieldTrait;
    use i18nTrait;

    public function __construct(
        private readonly AuthorizationChecker $authorizationChecker,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('group_offers')
            ->setSearchFields(['name'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add(MyGroupFilter::new('group'))
            ->add(EnumFilter::new('membership', OfferTypeType::class))
            ->add('name')
            ->add('active')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
        ;
    }

    public static function getEntityFqcn(): string
    {
        return GroupOffer::class;
    }

    /**
     * When a group admin is logged, we must restrict the groups he can access to.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        // admins can see everything
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($this->authorizationChecker->isAdmin()) {
            return $qb;
        }

        /** @var User $user */
        $user = $this->getUser();
        $qb->andWhere(\sprintf('%s.group IN (:groups)', $qb->getRootAliases()[0] ?? ''))
            ->setParameter(':groups', $user->getMyGroupsAsAdmin());

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        $idFIeld = IdField::new('id')
            ->setLabel('id')
            ->hideOnForm();
        $groupField = AssociationField::new('group')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                /** @var User $user */
                $user = $this->getUser();

                $qb = $queryBuilder->andWhere('entity.membership = :membership')
                    ->setParameter('membership', GroupMembership::CHARGED);
                if (!$user->isAdmin()) {
                    $qb->join('entity.userGroups', 'ug')
                    ->andWhere('ug.membership = :userMembership')
                    ->andWhere('ug.user = :user')
                    ->setParameter('userMembership', UserMembership::ADMIN)
                    ->setParameter('user', $user);
                }
            })
            ->setRequired(false);

        $nameField = TextField::new('name');
        $typeField = ChoiceField::new('type')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', OfferType::class)
            ->setChoices(OfferType::getAsArray());

        $priceField = MoneyField::new('price')
            ->setCurrencyPropertyPath('currency')
            ->setStoredAsCents();
        $currencyField = CurrencyField::new('currency');

        $activeField = BooleanField::new('active')
            ->setTemplatePath('easy_admin/field/boolean.html.twig')
        ;
        $createdAtField = DateTimeField::new('createdAt');
        $updatedAtField = DateTimeField::new('updatedAt');

        $panels = $this->getPanels();
        if ($pageName === Crud::PAGE_INDEX) {
            return [$groupField, $nameField, $typeField, $priceField, $activeField, $createdAtField, $updatedAtField];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $typeField->setChoices(OfferType::cases());

            return [
                $groupField,
                $nameField,
                $typeField,
                $priceField,
                $currencyField,
                $activeField,
            ];
        }

        // show

        return [
            $panels['information'],
            $groupField,
            $nameField,
            $typeField,
            $priceField,
            $currencyField,

            $panels['tech_information'],
            $idFIeld,
            $updatedAtField,
            $createdAtField,
        ];
    }
}
