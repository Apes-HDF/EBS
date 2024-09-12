<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\EasyAdmin\Form\Type\GroupMembershipType;
use App\EasyAdmin\Form\Type\GroupTypeType;
use App\Entity\Group;
use App\Entity\User;
use App\Enum\Group\GroupMembership;
use App\Enum\Group\GroupType;
use App\Form\Type\Security\GroupInvitationFormType;
use App\Helper\CsvExporter;
use App\Message\Command\Group\CreateGroupInvitationMessage;
use App\MessageBus\CommandBus;
use App\Repository\ConfigurationRepository;
use App\Security\Checker\AuthorizationChecker;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\CrudDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see GroupCrudControllerTest
 * @see GroupCrudControllerAsGroupAdminTest
 */
final class GroupCrudController extends AbstractCrudController implements GroupAdminSecuredCrudControllerInterface
{
    use FieldTrait;
    use i18nTrait;
    use FlashTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly CommandBus $commandBus,
        private readonly CsvExporter $csvExporter,
        private readonly TranslatorInterface $translator,
        private readonly FilterFactory $filterFactory,
        private readonly SluggerInterface $slugger,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('groups')
            ->setSearchFields(['name', 'description'])
            ->setDefaultSort(['id' => 'ASC'])
            ->overrideTemplate('crud/field/boolean', 'admin/field/services_enabled.html.twig')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add(EnumFilter::new('type', GroupTypeType::class))
            ->add(EnumFilter::new('membership', GroupMembershipType::class))
            ->add('name')
            ->add('description')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $exportAction = Action::new('export')
            ->linkToUrl(function () {
                /** @var AdminContext $context */
                $context = $this->getContext();

                return $this->adminUrlGenerator->setAll($context->getRequest()->query->all())
                    ->setEntityId(null)
                    ->setAction('export')
                    ->generateUrl();
            })
            ->addCssClass('btn btn-success')
            ->setIcon('fa fa-download')
            ->createAsGlobalAction()
        ;

        $listMembers = Action::new('listMembers')
            ->linkToUrl(function () {
                /** @var Group $group */
                $group = $this->getContext()?->getEntity()->getInstance();

                return $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(UserGroupCrudController::class)
                    ->set('filters[group]', $group->getId())
                    ->setAction('index')
                    ->generateUrl();
            })
            ->setIcon('fas fas-user-friends')
        ;
        $offersList = Action::new('offersList', 'offers_list')
           ->linkToUrl(function () {
               /** @var Group $group */
               $group = $this->getContext()?->getEntity()->getInstance();

               return $this->adminUrlGenerator
                   ->unsetAll()
                   ->setController(GroupOfferCrudController::class)
                   ->set('filters[group]', $group->getId())
                   ->setAction('index')
                   ->generateUrl();
           })
           ->displayIf(function () {
               /** @var Group $group */
               $group = $this->getContext()?->getEntity()->getInstance();

               return $group->getMembership()->isCharged();
           });

        $offersListIndexPage = Action::new('offersList', 'offers_list')
            ->linkToCrudAction('redirectToOffersList')
            ->displayIf(static function (Group $group) {
                return $group->getMembership()->isCharged();
            });

        $actions
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->add(Crud::PAGE_EDIT, $listMembers)
            ->add(Crud::PAGE_DETAIL, $listMembers)
            ->add(Crud::PAGE_DETAIL, $offersList)
            ->add(Crud::PAGE_INDEX, $offersListIndexPage);

        /** @var User $user */
        $user = $this->getUser();

        // display the invite link if we are an admin, the main group admin or the parameter is activated
        $inviteAction = Action::new('invite', 'invite', 'fa fa-user-plus')
            ->linkToCrudAction('invite')
            ->displayIf(fn (Group $group) => $this->authorizationChecker->isAdmin() || $group->isMainAdmin($user) || $group->isInvitationByAdmin())
        ;
        $actions
            ->add(Crud::PAGE_INDEX, $inviteAction);

        // group admin can't create a group from here but he can edit its groups
        if (!$this->authorizationChecker->isAdmin()) {
            $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW)
                ->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ;
        }

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
        ;
    }

    public function redirectToOffersList(AdminContext $context): Response
    {
        $group = $context->getEntity()->getInstance();
        $this->adminUrlGenerator
               ->unsetAll()
               ->setController(GroupOfferCrudController::class)
               ->set('filters[group]', $group->getId())
               ->setAction('index');

        return $this->redirect($this->adminUrlGenerator->generateUrl());
    }

    public static function getEntityFqcn(): string
    {
        return Group::class;
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
        $qb->andWhere(\sprintf('%s.id IN (:groups)', $qb->getRootAliases()[0] ?? ''))
            ->setParameter(':groups', $user->getMyGroupsAsAdmin());

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        $idFIeld = IdField::new('id')
            ->setLabel('id')
            ->hideOnForm();

        $typeField = ChoiceField::new('type')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', GroupType::class)
            ->setChoices(GroupType::getAsArray());
        $membershipField = ChoiceField::new('membership')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', GroupMembership::class)
            ->setChoices(GroupMembership::getAsArray());

        if ($this->configurationRepository->getInstanceConfigurationOrCreate()->getServicesEnabled()) {
            $servicesEnabledField = BooleanField::new('servicesEnabled')
                ->renderAsSwitch()
                ->setFormTypeOption('attr', [
                    'data-controller' => 'admin-parentgroup',
                    'data-admin-parentgroup-target' => 'servicesEnabledField',
                ])
                ->addWebpackEncoreEntries('admin');
        }

        $parentField = AssociationField::new('parent')
            ->setRequired(false)
            ->addWebpackEncoreEntries('admin')
            ->setFormTypeOption('attr', [
                'data-controller' => 'admin-parentgroup',
                'data-admin-parentgroup-target' => 'parentField',
            ])
        ;
        $childrenField = AssociationField::new('children');
        $usersField = AssociationField::new('userGroups')
            ->setTemplatePath('admin/group/user_groups_field.html.twig');

        $nameField = TextField::new('name');
        $descriptionField = TextareaField::new('description');

        $url = UrlField::new('url');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        $invitationByAdminField = BooleanField::new('invitationByAdmin')->renderAsSwitch(false);
        $panels = $this->getPanels();

        if ($pageName === Crud::PAGE_INDEX) {
            $fields = [$nameField, $typeField, $parentField, $membershipField, $usersField, $createdAt, $updatedAt];

            if ($this->configurationRepository->getInstanceConfigurationOrCreate()->getServicesEnabled()) {
                array_splice($fields, 3, 0, [$servicesEnabledField]);
            }

            return $fields;
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $typeField->setChoices(GroupType::cases());
            $membershipField->setChoices(GroupMembership::cases());

            $fields = [
                $nameField,
                $typeField,
                $membershipField,
                $parentField,
                $descriptionField,
                $url,
                $invitationByAdminField,
                $membershipField,
            ];

            if ($this->configurationRepository->getInstanceConfigurationOrCreate()->getServicesEnabled()) {
                array_splice($fields, 3, 0, [$servicesEnabledField]);
            }

            return $fields;
        }

        // show

        $fields = [
            $panels['information'],
            $nameField,
            $parentField,
            $childrenField,
            $typeField,
            $membershipField,
            $descriptionField,
            $invitationByAdminField,

            $panels['tech_information'],
            $idFIeld,
            $updatedAt,
            $createdAt,
        ];

        if ($this->configurationRepository->getInstanceConfigurationOrCreate()->getServicesEnabled()) {
            array_splice($fields, 2, 0, [$servicesEnabledField]);
        }

        return $fields;
    }

    /**
     * Custom action that allows sending invitations to users. We only need the email
     * here as the rest of the process is handled by the step 2 form of the account
     * creation workflow.
     *
     * @see GroupCrudControllerTest::testInviteActionSuccess()
     */
    public function invite(Request $request): Response
    {
        /** @var Group $group */
        $group = $this->getContext()?->getEntity()->getInstance();
        $user = new User();
        $form = $this->createForm(GroupInvitationFormType::class, $user)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateGroupInvitationMessage($user->getEmail(), $group->getId()));
            $this->addFlashSuccess($this->getI18nPrefix().'.invite.flash.success');

            return $this->redirect($this->adminUrlGenerator->unsetAll()->setController(self::class)->generateUrl());
        }

        return $this->render('admin/group/invite.html.twig', compact('form', 'group'));
    }

    /**
     * For now we export exactly what we see in the list to avoid security problems.
     */
    public function export(AdminContext $context): Response
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        /** @var CrudDto $crud Crud is defined here */
        $crud = $context->getCrud();

        $filters = $this->filterFactory->create($crud->getFiltersConfig(), $fields, $context->getEntity());
        /** @var SearchDto $search */
        $search = $context->getSearch();
        $queryBuilder = $this->createIndexQueryBuilder($search, $context->getEntity(), $fields, $filters);

        $fileName = $this->slugger->slug($this->translator->trans('menu.groups', [], DashboardController::DOMAIN));

        return $this->csvExporter->createResponseFromQueryBuilder($queryBuilder, $fields, $fileName.'.csv');
    }
}
