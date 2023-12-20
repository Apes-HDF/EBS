<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\FieldTrait;
use App\Entity\Menu;
use App\Flysystem\EasyAdminHelper;
use App\Flysystem\MediaManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function Symfony\Component\Translation\t;

abstract class AbstractMenuCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;

    abstract public function getPageTitle(): string;

    abstract public function getEntityLabelInPlural(): string;

    abstract public function getEntityLabelInSingular(): string;

    abstract public function getMenuItemsIndex(): int;

    /**
     * @return class-string
     */
    abstract public function getMenuItemCrudControllerClass(): string;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly FilesystemOperator $defaultStorage,
        private readonly EasyAdminHelper $easyAdminHelper,
        private readonly MediaManager $mediaManager,
        #[Autowire('%base_path%')]
        private readonly string $menuBasePath,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, t($this->getPageTitle(), [], DashboardController::DOMAIN))
            ->setPageTitle(Crud::PAGE_DETAIL, t($this->getEntityLabelInSingular(), [], DashboardController::DOMAIN))
            ->setEntityLabelInPlural($this->getEntityLabelInPlural())
            ->setEntityLabelInSingular($this->getEntityLabelInSingular())
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'easy_admin/crud/form_theme.html.twig',
            ])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Better button label for this kind of page
        $actions->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
            return $action->setLabel('action.save');
        });

        $itemsListUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->getMenuItemCrudControllerClass())
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', $this->getMenuItemsIndex())
            ->generateUrl();

        $itemsList = Action::new('editMenuItems', 'menu.action.items_list')
            ->linkToUrl($itemsListUrl);

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, $itemsList)
        ;
    }

    public static function getEntityFqcn(): string
    {
        return Menu::class;
    }

    /**
     * Return all possible fields.
     *
     * @return array<FieldInterface>
     */
    public function getFields(string $pageName): array
    {
        $logoField = ImageField::new('logo')
            ->setBasePath($this->menuBasePath) // correctly set the formatted value available in the template
            ->setUploadDir('public'.$this->menuBasePath)
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setFormTypeOption('upload_new', $this->easyAdminHelper->getUploadNewCallback($this->defaultStorage))
            ->setFormTypeOption('upload_delete', $this->easyAdminHelper->getUploadDeleteCallback($this->defaultStorage))
            ->setFormTypeOption('constraints', $this->mediaManager->getFileConstraints())
            ->setTemplatePath('easy_admin/field/flysystem_image.html.twig')
            ->setHelp($this->mediaManager->getHelpMessage())
        ;

        $code = TextField::new('code');
        $items = CollectionField::new('items')
            ->useEntryCrudForm(MenuItemCrudController::class)
        ;
        $itemsCount = IntegerField::new('itemsCount');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        return compact(
            'logoField',
            'items',
            'itemsCount',
            'code',
            'createdAt',
            'updatedAt',
        );
    }

    public function configureFields(string $pageName): iterable
    {
        $panels = $this->getPanels();

        [
            'logoField' => $logoField,
            'itemsCount' => $itemsCount,
            'code' => $code,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_INDEX) {
            return [$code, $logoField, $itemsCount, $createdAt, $updatedAt];
        }

        if ($pageName === Crud::PAGE_DETAIL) {
            return [
                $panels['information'],
                $logoField,
                $itemsCount,
                $panels['tech_information'],
                $code,
                $createdAt,
                $updatedAt,
            ];
        }

        // edit page
        return [$logoField];
    }
}
