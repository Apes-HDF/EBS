<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\i18nTrait;
use App\Doctrine\Manager\UserManager;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\User\GroupFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\Entity\Category;
use App\Entity\User;
use App\Enum\User\UserType;
use App\Flysystem\EasyAdminHelper;
use App\Flysystem\MediaManager;
use App\Helper\CsvExporter;
use App\Mailer\AppMailer;
use App\Mailer\Email\Admin\PromoteToAdmin\PromoteToAdminEmail;
use App\Repository\ConfigurationRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\CrudDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use League\Flysystem\FilesystemOperator;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractUserCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;
    use i18nTrait;

    public AppMailer $mailer;

    private const SWITCH_USER_PARAMETER = '_switch_user'; // @see security.yaml

    abstract public function getUserType(): UserType;

    abstract public function getEntityLabelInPlural(): string;

    abstract public function getEntityLabelInSingular(): string;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly Security $security,
        private readonly UserManager $userManager,
        private readonly CsvExporter $csvExporter,
        private readonly TranslatorInterface $translator,
        private readonly FilterFactory $filterFactory,
        private readonly SluggerInterface $slugger,
        private readonly FilesystemOperator $userStorage,
        private readonly EasyAdminHelper $easyAdminHelper,
        private readonly MediaManager $mediaManager,
        #[Autowire('%user_base_path%')]
        private readonly string $userBasePath,
        AppMailer $mailer,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
        $this->mailer = $mailer;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural($this->getEntityLabelInPlural())
            ->setEntityLabelInSingular($this->getEntityLabelInSingular())
            ->setSearchFields(['email', 'firstname', 'lastname', 'name'])
            ->setDefaultSort(['id' => 'ASC'])
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'easy_admin/crud/form_theme.html.twig',
            ])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add(GroupFilter::new('group'))
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('enabled')
            ->add('createdAt')
            ->add('updatedAt')
            ->add(DateTimeFilter::new('loginAt')
                ->setFormTypeOption('value_type_options', ['widget' => 'choice'])
            )
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $currentUser = $this->security->getUser();

        $connectAs = Action::new('connectAs', 'action.connectAs', 'fas fa-sign-in-alt')
            ->linkToCrudAction('connectAs')
            ->displayIf(fn (User $user) => $currentUser !== $user && $user->isEnabled() && $user->isEmailConfirmed());
        $actions
            ->add(Crud::PAGE_INDEX, $connectAs);

        $promoteToAdmin = Action::new('promoteToAdmin', 'action.promoteToAdmin')
            ->linkToCrudAction('promoteToAdmin')
            ->displayIf(fn (User $user) => !$user->isAdmin() && !$user->isMainAdminAccount());
        $actions
            ->add(Crud::PAGE_INDEX, $promoteToAdmin);

        $deleteCallback = function (Action $action) use ($currentUser) {
            return $action->displayIf(fn (User $user) => $currentUser !== $user && !$user->isMainAdminAccount());
        };
        $actions->update(Crud::PAGE_INDEX, 'delete', $deleteCallback);
        $actions->update(Crud::PAGE_DETAIL, 'delete', $deleteCallback);

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
        $actions->add(Crud::PAGE_INDEX, $exportAction);

        $viewPayments = Action::new('payments')
            ->linkToUrl(function () {
                /** @var AdminContext $context */
                $context = $this->getContext();
                /** @var User $user */
                $user = $context->getEntity()->getInstance();

                return $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(PaymentCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters[user]', $user->getId())
                    ->generateUrl();
            })
            ->displayIf(fn (User $user) => !$user->getPayments()->isEmpty())
            ->setIcon('fas fa-credit-card')
        ;
        $actions->add(Crud::PAGE_DETAIL, $viewPayments);

        return $actions
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    /**
     * Impersonate action so we can do some more processing before changing user.
     */
    public function connectAs(AdminContext $context): Response
    {
        /** @var User $targetUser */
        $targetUser = $context->getEntity()->getInstance();

        $message = new TranslatableMessage(
            'flash.warning.connectAs',
            ['%target_user%' => $targetUser->getUserIdentifier()],
            DashboardController::DOMAIN
        );

        $this->addFlash(
            'warning',
            $message
        );
        $route = $targetUser->isAdmin() ? 'admin' : 'home'; // if user is not admin redirect to home page

        return $this->redirectToRoute($route, [self::SWITCH_USER_PARAMETER => $targetUser->getUserIdentifier()]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function promoteToAdmin(AdminContext $context): Response
    {
        /** @var User $targetUser */
        $targetUser = $context->getEntity()->getInstance();
        $targetUser->setType(UserType::ADMIN)->promoteToAdmin();
        $this->userManager->save($targetUser, true);

        $message = new TranslatableMessage(
            'flash.success.promoteToAdmin',
            ['%target_user%' => $targetUser->getUserIdentifier()],
            DashboardController::DOMAIN
        );

        $this->addFlash(
            'success',
            $message
        );

        $userContext = [];
        $userContext['user'] = $targetUser;
        $this->mailer->send(PromoteToAdminEmail::class, $userContext);

        return $this->redirect(
            $this
                ->adminUrlGenerator
                ->unsetAll()
                ->setController(AdministratorCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * We consider the admin know what he does, in case of error he can simply modify
     * the email.
     */
    public function createEntity(string $entityFqcn): User
    {
        /** @var User $user */
        $user = new $entityFqcn();
        $user->setType($this->getUserType());
        $this->userManager->finalizeAccountCreateStep2($user);

        return $user;
    }

    /**
     * Only display a given user type.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $alias = $qb->getRootAliases()[0] ?? null;
        $qb->andWhere($alias.'.type = :type')
            ->setParameter('type', $this->getUserType());

        return $qb;
    }

    /**
     * Return all possible user fields.
     *
     * @return array<FieldInterface>
     */
    public function getFields(string $pageName): array
    {
        $i18prefix = $this->getI18nPrefix(self::class);
        $idField = IdField::new('id')
            ->setLabel('id')
            ->hideOnForm();

        $emailField = EmailField::new('email');
        $firstNameField = TextField::new('firstname')->setRequired(true);
        $lastNameField = TextField::new('lastname')->setRequired(true);
        $nameField = TextField::new('name')->setRequired(true);

        $plainPassword = TextField::new('plainPassword')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'required' => $pageName === Crud::PAGE_NEW,
                'first_options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
            ]);

        $enabledField = $this->getSimpleBooleanField('enabled');
        $emailConfirmedField = $this->getSimpleBooleanField('emailConfirmed');

        $typeField = ChoiceField::new('type')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', UserType::class)
            ->setChoices(UserType::getAsArray());

        $loginAt = DateTimeField::new('loginAt');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        $avatarField = ImageField::new('avatar')
            ->setBasePath($this->userBasePath) // correctly set the formatted value available in the template
            ->setUploadDir('public'.$this->userBasePath)
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setFormTypeOption('upload_new', $this->easyAdminHelper->getUploadNewCallback($this->userStorage))
            ->setFormTypeOption('upload_delete', $this->easyAdminHelper->getUploadDeleteCallback($this->userStorage))
            ->setFormTypeOption('constraints', $this->mediaManager->getFileConstraints())
            ->setTemplatePath('easy_admin/field/flysystem_image.html.twig')
            ->setHelp($this->mediaManager->getHelpMessage())
        ;

        $phoneNumberField = TextField::new('phone')
            ->setFormType(PhoneNumberType::class)
            ->setFormTypeOptions([
                'format' => PhoneNumberFormat::INTERNATIONAL,
                'required' => false,
            ])
            ->setHelp($i18prefix.'.field.phone.help')
        ;

        $scheduleField = TextField::new('schedule');
        $categoryField = AssociationField::new('category')
            ->setFormTypeOption('choice_label', function (Category $category) {
                return $this->translator->trans($category->getType()->name, [], 'admin').' / '.$category->getName();
            })
            ->setRequired(false)
        ;
        $descriptionField = TextareaField::new('description');
        $smsNotificationsField = BooleanField::new('smsNotifications');
        $vacationModeField = BooleanField::new('vacationMode');
        $addressField = AssociationField::new('address');
        $groupsCountField = AssociationField::new('userGroups')->setLabel('Groups number');
        $membershipPaidField = BooleanField::new('membershipPaid');
        $startAt = DateField::new('startAt');
        $endAt = DateField::new('endAt');
        $expiresInField = IntegerField::new('expiresIn')
            ->formatValue(function ($value) {
                return $value !== null ? $this->translator->trans($this->getI18nPrefix().'.expires_in.formatted_value', ['%days%' => $value], 'admin') : '';
            })
            ->setFormTypeOptions([
                'attr' => ['readonly' => 'readonly'],
                'required' => false,
            ])
        ;
        $payedAt = DateTimeField::new('payedAt');
        $offerField = AssociationField::new('platformOffer');

        return compact(
            'idField',
            'emailField',
            'firstNameField',
            'lastNameField',
            'nameField',
            'plainPassword',
            'enabledField',
            'emailConfirmedField',
            'typeField',
            'loginAt',
            'createdAt',
            'updatedAt',
            'avatarField',
            'phoneNumberField',
            'scheduleField',
            'categoryField',
            'descriptionField',
            'smsNotificationsField',
            'vacationModeField',
            'addressField',
            'groupsCountField',
            'membershipPaidField',
            'startAt',
            'endAt',
            'expiresInField',
            'payedAt',
            'offerField',
        );
    }

    /**
     * For now, we export exactly what we see in the list to avoid security problems.
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
        $fileName = $this->slugger->slug($this->translator->trans($this->getEntityLabelInPlural(), [], DashboardController::DOMAIN));

        return $this->csvExporter->createResponseFromQueryBuilder($queryBuilder, $fields, $fileName.'.csv');
    }

    /**
     * We can't delete the main admin account or ourself. This is an additional
     * protection as the delete button is already disabled in the list.
     */
    public function delete(AdminContext $context)
    {
        /** @var User $userToDelete */
        $userToDelete = $context->getEntity()->getInstance();
        $currentUser = $this->security->getUser();
        if ($userToDelete === $currentUser || $userToDelete->isMainAdminAccount()) {
            throw $this->createAccessDeniedException('Cannot delete this user (self or main admin account).');
        }

        return parent::delete($context);
    }

    /**
     * Special process for the phone number as it uses a custom form type.
     *
     * @see EditProfileFormType::onPostSubmit
     */
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /** @var User $entityInstance */
        $entityInstance->changePhoneNumber($entityInstance->phone);

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * We need to normalize the email to make work the unique entity properly.
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = $this->container->get(FormFactory::class)->createNewFormBuilder($entityDto, $formOptions, $context);
        $this->userManager->addEmailNormalizeSubmitEvent($builder);

        return $builder;
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = $this->container->get(FormFactory::class)->createEditFormBuilder($entityDto, $formOptions, $context);
        $this->userManager->addEmailNormalizeSubmitEvent($builder);

        return $builder;
    }

    public function platformRequiresGlobalPayment(): bool
    {
        return $this->configurationRepository->getInstanceConfigurationOrCreate()->getPaidMembership();
    }
}
