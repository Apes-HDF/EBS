<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\i18nTrait;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\EasyAdmin\Form\Type\ProductStatusType;
use App\EasyAdmin\Form\Type\ProductVisibilityType;
use App\Entity\Product;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use App\Flysystem\EasyAdminHelper;
use App\Flysystem\MediaManager;
use App\Form\Type\Product\AbstractProductFormType;
use App\Helper\CsvExporter;
use App\Repository\CategoryRepository;
use App\Repository\GroupRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\CrudDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractProductCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;
    use i18nTrait;

    abstract public function getProductType(): ProductType;

    abstract public function getEntityLabelInPlural(): string;

    abstract public function getEntityLabelInSingular(): string;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly FilesystemOperator $productStorage,
        private readonly EasyAdminHelper $easyAdminHelper,
        private readonly MediaManager $mediaManager,
        #[Autowire('%product_base_path%')]
        private readonly string $productBasePath,
        private readonly CsvExporter $csvExporter,
        private readonly TranslatorInterface $translator,
        private readonly FilterFactory $filterFactory,
        private readonly SluggerInterface $slugger,
        protected readonly GroupRepository $groupRepository,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural($this->getEntityLabelInPlural())
            ->setEntityLabelInSingular($this->getEntityLabelInSingular())
            ->setSearchFields(['name', 'description'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'easy_admin/crud/form_theme.html.twig',
            ])
            ->setFormOptions([
                'validation_groups' => [AbstractProductFormType::class],
            ])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add(EnumFilter::new('status', ProductStatusType::class))
            ->add(EnumFilter::new('visibility', ProductVisibilityType::class))
            ->add('category')
            ->add('owner')
            ->add('name')
            ->add('description')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $onBreak = Action::new('onBreak', 'action.onBreak')
            ->linkToCrudAction('changeStatus')
            ->displayIf(static function (Product $product) {
                return $product->isActive();
            });

        $activate = Action::new('activate', 'action.activate')
            ->linkToCrudAction('changeStatus')
            ->displayIf(static function (Product $product) {
                return $product->isPaused();
            });

        $availability = Action::new('availability', 'action.availability')
            ->linkToCrudAction('linkToProductAvailabilityPage');

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

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_INDEX, $onBreak)
            ->add(Crud::PAGE_INDEX, $activate)
            ->add(Crud::PAGE_DETAIL, $availability)
            ->add(Crud::PAGE_INDEX, $exportAction);
    }

    private function redirectToObjectCrudPage(): RedirectResponse
    {
        $this->adminUrlGenerator->setController(ObjectCrudController::class)->setAction('index')->removeReferrer()->setEntityId(null);

        return $this->redirect($this->adminUrlGenerator->generateUrl());
    }

    public function linkToProductAvailabilityPage(): Response
    {
        return $this->render('/admin/product/availability_product.html.twig');
    }

    public function changeStatus(AdminContext $context): Response
    {
        /** @var Product $product */
        $product = $context->getEntity()->getInstance();
        if ($product->isPaused()) {
            $product->setStatus(ProductStatus::ACTIVE);
        } else {
            $product->setStatus(ProductStatus::PAUSED);
        }
        $this->productRepository->save($product, true);

        return $this->redirectToObjectCrudPage();
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

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function createEntity(string $entityFqcn): Product
    {
        /** @var Product $product */
        $product = new $entityFqcn();
        $product->setType($this->getProductType());

        return $product;
    }

    /**
     * Only display a given product type.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $alias = $qb->getRootAliases()[0] ?? null;
        $qb->andWhere($alias.'.type = :type')
            ->setParameter('type', $this->getProductType());

        return $qb;
    }

    /**
     * Return all possible product fields.
     *
     * @return array<FieldInterface>
     */
    public function getFields(string $pageName): array
    {
        $idField = IdField::new('id')
            ->setLabel('id')
            ->hideOnForm();

        $typeField = ChoiceField::new('type')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', ProductType::class)
            ->setChoices(ProductType::getAsArray());

        $statusField = ChoiceField::new('status')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', ProductStatus::class)
            ->setChoices(ProductStatus::getAsArray());

        $visibilityField = ChoiceField::new('visibility')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', ProductVisibility::class)
            ->setChoices(ProductVisibility::getAsArray());
        $groupsField = AssociationField::new('groups')->onlyOnForms();
        $groupsFieldList = CollectionField::new('groups')->hideOnForm();

        $ownerField = AssociationField::new('owner');
        $categoryField = AssociationField::new('category')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $this->categoryRepository->addTypeFilter($queryBuilder, $this->getProductType());
            })
        ;

        $nameField = TextField::new('name');
        $descriptionField = TextareaField::new('description');

        // objects
        $ageField = TextField::new('age');
        $depositField = MoneyField::new('deposit')
            ->setCurrencyPropertyPath('currency')
            ->setStoredAsCents()
            ->setNumDecimals(0)
        ;
        $currencyField = CurrencyField::new('currency');

        // services
        $durationField = TextField::new('duration');

        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        $imageField = ImageField::new('images')
            ->setBasePath($this->productBasePath) // correctly set the formatted value available in the template
            ->setUploadDir('public'.$this->productBasePath)
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setFormTypeOption('upload_new', $this->easyAdminHelper->getUploadNewCallback($this->productStorage))
            ->setFormTypeOption('upload_delete', $this->easyAdminHelper->getUploadDeleteCallback($this->productStorage))
            ->setFormTypeOption('required', false)
            ->setFormTypeOption('multiple', true)
            ->setFormTypeOption('constraints', $this->mediaManager->getImageArrayConstraints())
            ->setTemplatePath('easy_admin/field/flysystem_images.html.twig')
            ->setCustomOption('first_image_only', true)
            ->setHelp($this->mediaManager->getHelpMessage())
            ->setSortable(false)
        ;

        $addressField = TextField::new('address');

        if ($pageName === Crud::PAGE_DETAIL) {
            /** @var Product $product */
            $product = $this->getContext()?->getEntity()->getInstance();

            $addressField = TextField::new('address')->setValue($product->getOwner()->getAddress()?->getDisplayName());
        }

        $preferredLoanDuration = TextField::new('preferredLoanDuration');

        return compact(
            'idField',
            'typeField',
            'statusField',
            'visibilityField',
            'groupsField',
            'groupsFieldList',
            'ownerField',
            'categoryField',
            'nameField',
            'descriptionField',
            'ageField',
            'durationField',
            'depositField',
            'currencyField',
            'createdAt',
            'updatedAt',
            'imageField',
            'addressField',
            'preferredLoanDuration',
        );
    }
}
