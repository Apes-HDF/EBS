<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\UuidFilter;
use App\Entity\Category;
use App\Enum\Product\ProductType;
use App\Flysystem\EasyAdminHelper;
use App\Flysystem\MediaManager;
use App\Repository\CategoryRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCategoryCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;
    use FlashTrait;
    use i18nTrait;

    abstract public function getCategoryType(): ProductType;

    abstract public function getEntityLabelInPlural(): string;

    public function getCrudControllerClass(): string
    {
        return $this::class;
    }

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly FilesystemOperator $categoryStorage,
        private readonly EasyAdminHelper $easyAdminHelper,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly MediaManager $mediaManager,
        #[Autowire('%category_base_path%')]
        private readonly string $categoryBasePath,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural($this->getEntityLabelInPlural())
            ->setEntityLabelInSingular($this->getEntityLabelInSingular())
            ->setSearchFields(['name'])
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'easy_admin/crud/form_theme.html.twig',
            ])
            ->setPaginatorPageSize(100) // display everyhting to have the while hierarchical view
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add('name')
            ->add('parent')
            ->add('enabled')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $moveDownAction = Action::new('down', $this->getI18nPrefix(self::class).'.menu.action.move_down', 'fa-sharp fa-solid fa-arrow-down')
            ->linkToCrudAction('moveDown');
        $moveUpAction = Action::new('up', $this->getI18nPrefix(self::class).'.menu.action.move_up', 'fa-sharp fa-solid fa-arrow-up')
            ->linkToCrudAction('moveUp');

        // don't display the delete link if the item has children
        $deleteAction = Action::new('delete', 'menu.action.delete')
            ->linkToCrudAction('delete')
            ->displayIf(static function (Category $category) {
                return !$category->hasChildren();
            })
            ->setCssClass('dropdown-item action-delete text-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_INDEX, $moveDownAction)
            ->add(Crud::PAGE_INDEX, $moveUpAction)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $deleteAction)
        ;
    }

    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function getEntityLabelInSingular(): string
    {
        return 'category';
    }

    public function createEntity(string $entityFqcn): Category
    {
        /** @var Category $category */
        $category = new $entityFqcn();
        $category->setType($this->getCategoryType());

        return $category;
    }

    /**
     * Only display a given type.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $alias = $qb->getRootAliases()[0] ?? null;
        $qb->andWhere($alias.'.type = :type')
            ->setParameter('type', $this->getCategoryType())
            ->orderBy($alias.'.lft', 'ASC')
        ;

        return $qb;
    }

    /**
     * Return all possible fields.
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

        $parentField = AssociationField::new('parent')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $this->categoryRepository->addTypeFilter($queryBuilder, $this->getCategoryType());
            })
            ->setLabel('category.parent')
            ->setRequired(false)
        ;

        $nameField = TextField::new('name')
            ->formatValue(static function ($value, Category $category) {
                return $category->getNameWithIndent();
            });

        $enabledField = $this->getSimpleBooleanField('enabled');

        $imageField = ImageField::new('image')
            ->setLabel('image.default')
            ->setHelp('image.help')
            ->setBasePath($this->categoryBasePath) // correctly set the formatted value available in the template
            ->setUploadDir('public'.$this->categoryBasePath)
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setFormTypeOption('upload_new', $this->easyAdminHelper->getUploadNewCallback($this->categoryStorage))
            ->setFormTypeOption('upload_delete', $this->easyAdminHelper->getUploadDeleteCallback($this->categoryStorage))
            ->setFormTypeOption('constraints', $this->mediaManager->getFileConstraints())
            ->setTemplatePath('easy_admin/field/flysystem_image.html.twig')
            ->setHelp($this->mediaManager->getHelpMessage())
        ;

        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        return compact(
            'idField',
            'parentField',
            'typeField',
            'nameField',
            'enabledField',
            'imageField',
            'createdAt',
            'updatedAt'
        );
    }

    public function configureFields(string $pageName): iterable
    {
        $panels = $this->getPanels();

        [
            'idField' => $idField,
            'typeField' => $typeField,
            'parentField' => $parentField,
            'nameField' => $nameField,
            'enabledField' => $enabledField,
            'imageField' => $imageField,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
        ] = $this->getFields($pageName);

        // list
        if ($pageName === Crud::PAGE_INDEX) {
            return [$nameField, $parentField, $enabledField, $imageField, $createdAt];
        }

        // forms
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            return [$nameField, $parentField, $enabledField, $imageField];
        }

        // detail

        return [
            $panels['information'],
            $parentField,
            $typeField,
            $nameField,
            $enabledField,
            $imageField,
            $panels['tech_information'],
            $idField,
            $createdAt,
            $updatedAt,
       ];
    }

    public function moveUp(AdminContext $context): Response
    {
        /** @var Category $item */
        $item = $context->getEntity()->getInstance();
        $this->categoryRepository->moveUp($item);

        return $this->redirectToObjectCrudPage();
    }

    public function moveDown(AdminContext $context): Response
    {
        /** @var Category $item */
        $item = $context->getEntity()->getInstance();
        $this->categoryRepository->moveDown($item);

        return $this->redirectToObjectCrudPage();
    }

    private function redirectToObjectCrudPage(): RedirectResponse
    {
        $this->addFlashSuccess($this->getI18nPrefix(self::class).'.move.success');
        $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->getCrudControllerClass())
            ->setAction('index');

        return $this->redirect($this->adminUrlGenerator->generateUrl());
    }
}
