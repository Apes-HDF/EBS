<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\i18nTrait;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Filter\User\MyUsersFilter;
use App\EasyAdmin\Filter\UserGroup\MyGroupFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\EasyAdmin\Form\Type\UserMembershipType;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\Group\UserMembership;
use App\Mailer\AppMailer;
use App\Mailer\Email\Admin\UserGroup\AdminPromotionEmail;
use App\Mailer\Email\Admin\UserGroup\MainAdminPromotionEmail;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Security\Checker\AuthorizationChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see UserGroupCrudControllerTest
 * @see UserGroupCrudControllerAsGroupAdminTest
 */
final class UserGroupCrudController extends AbstractCrudController implements GroupAdminSecuredCrudControllerInterface
{
    use FieldTrait;
    use i18nTrait;
    use SmsNotifierTrait;

    private UserGroup $previousUserGroup;

    public function __construct(
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly Security $security,
        private readonly AppMailer $mailer,
        private readonly TranslatorInterface $translator,
        private readonly SmsNotifier $notifier,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('user_groups')
            ->setSearchFields(['group'])
            ->setDefaultSort(['user' => 'ASC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add(UuidFilter::new('id'))
            ->add(MyUsersFilter::new('user'))
            ->add(MyGroupFilter::new('group'))
            ->add(EnumFilter::new('membership', UserMembershipType::class))
        ;

        if ($this->authorizationChecker->isAdmin()) {
            $filters->add('mainAdminAccount');
        }

        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $currentUser = $this->security->getUser();
        $actions->update(Crud::PAGE_INDEX, 'delete', function (Action $action) use ($currentUser) {
            return $action->displayIf(fn (UserGroup $usergroup) => $currentUser !== $usergroup->getUser() && !$usergroup->isMainAdminAccount());
        });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
        ;
    }

    public static function getEntityFqcn(): string
    {
        return UserGroup::class;
    }

    /**
     * When a group admin is logged, we must restrict the groups he can access to.
     *
     * @see GroupCrubController
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
        /** @var User $user */
        $user = $this->getUser();

        $idFIeld = IdField::new('id')
            ->setLabel('id')
            ->hideOnForm();

        $userField = AssociationField::new('user');
        if (!$this->authorizationChecker->isAdmin()) {
            $userField
                ->setTemplatePath('easy_admin/field/user_email.html.twig')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) use ($user) {
                    $queryBuilder
                        ->innerJoin('entity.userGroups', 'ug')
                        ->andWhere('ug.group IN (:groups)')
                        ->setParameter(':groups', $user->getMyGroupsAsAdmin());
                });
        }

        $groupField = AssociationField::new('group');
        if (!$this->authorizationChecker->isAdmin()) {
            $groupField
                 ->setQueryBuilder(function (QueryBuilder $queryBuilder) use ($user) {
                     $queryBuilder
                         ->andWhere('entity.id IN (:groups)')
                         ->setParameter(':groups', $user->getMyGroupsAsAdmin());
                 });
        }

        $membershipField = ChoiceField::new('membership')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', UserMembership::class)
            ->setChoices(UserMembership::getAsArray());
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $startAt = DateField::new('startAt');
        $endAt = DateField::new('endAt');
        $expiresInField = IntegerField::new('expiresIn')
            ->formatValue(function ($value) {
                return $value !== null ? $this->translator->trans($this->getI18nPrefix().'.expires_in.formatted_value', ['%days%' => $value], 'admin') : '';
            })
        ;
        $payedAt = DateTimeField::new('payedAt');

        $mainAdminAccountField = BooleanField::new('mainAdminAccount')
            ->setTemplatePath('easy_admin/field/boolean_check_only.html.twig');

        $panels = $this->getPanels();
        if ($pageName === Crud::PAGE_INDEX) {
            return [$groupField, $userField, $membershipField, $mainAdminAccountField, $payedAt, $startAt, $endAt, $expiresInField, $createdAt, $updatedAt];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $membershipField->setChoices(UserMembership::cases());
            $editionFields = [
                $groupField,
                $userField,
                $membershipField,
                $startAt,
                $endAt,
            ];

            if ($this->authorizationChecker->isAdmin()) {
                $editionFields[] = $mainAdminAccountField;
            }

            return $editionFields;
        }

        // show

        return [
            $panels['information'],
            $groupField,
            $userField,
            $membershipField,
            $startAt,
            $endAt,
            $payedAt,
            $mainAdminAccountField,

            $panels['tech_information'],
            $idFIeld,
            $updatedAt,
            $createdAt,
        ];
    }

    public function createEditForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        /** @var UserGroup $previousUserGroup */
        $previousUserGroup = clone $entityDto->getInstance();
        $this->previousUserGroup = $previousUserGroup;

        return parent::createEditForm($entityDto, $formOptions, $context);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var UserGroup $entityInstance */
        parent::updateEntity($entityManager, $entityInstance);
        $user = $entityInstance->getUser();
        $membership = $entityInstance->getMembership();
        $context = [];
        $context['group'] = $entityInstance->getGroup();
        $context['user'] = $user;

        // don't send both emails
        if ($entityInstance->isMainAdminAccount() && !$this->previousUserGroup->isMainAdminAccount()) {
            $this->mailer->send(MainAdminPromotionEmail::class, $context);
            $this->sendSms($user, MainAdminPromotionEmail::class);
        } elseif ($membership !== $this->previousUserGroup->getMembership() && $membership->isAdmin()) {
            $this->mailer->send(AdminPromotionEmail::class, $context);
            $this->sendSms($user, AdminPromotionEmail::class);
        }
    }
}
