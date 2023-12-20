<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Form\Type\SocialMediaTypeType;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Enum\Menu\LinkType;
use App\Enum\SocialMediaType;
use App\Repository\MenuItemRepository;
use App\Repository\MenuRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractMenuItemCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    abstract public function getEntityLabelInPlural(): string;

    abstract public function getEntityLabelInSingular(): string;

    abstract public function getCode(): string;

    abstract public function getMenuControllerClass(): string;

    abstract public function getMenuItemsControllerClass(): string;

    abstract public function getNewMenuItemLinkController(): string;

    abstract public function getNewMenuItemIconController(): string;

    final public const MENUS = [
        Menu::MENU => 1,
        Menu::FOOTER => 2,
    ];

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly MenuRepository $menuRepository,
        private readonly MenuItemRepository $menuItemRepository,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural($this->getEntityLabelInPlural())
            ->setEntityLabelInSingular($this->getEntityLabelInSingular())
            ->setDefaultSort(['parent' => 'DESC', 'position' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('link')
            ->add('parent')
            ->add(EnumFilter::new('mediaType', SocialMediaTypeType::class))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $editLogoUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->getMenuControllerClass())
            ->setEntityId(self::MENUS[$this->getCode()])
            ->set('crudAction', Crud::PAGE_EDIT)
            ->generateUrl();

        $editLogo = Action::new('editMenuItems', 'menu.action.edit_logo', 'fas fa-edit')
            ->linkToUrl($editLogoUrl)->createAsGlobalAction();

        $moveDownPosition = Action::new('down', 'menu.action.down_item', 'fa-sharp fa-solid fa-arrow-down')
            ->linkToCrudAction('moveDownPosition');

        $moveUpPosition = Action::new('up', 'menu.action.up_item', 'fa-sharp fa-solid fa-arrow-up')
            ->linkToCrudAction('moveUpPosition')
            ->displayIf(static function (MenuItem $item) {
                return !$item->isFirst();
            });

        // don't display the delete link if the item has children
        $deleteAction = Action::new('delete', 'menu.action.delete')
            ->linkToCrudAction('delete')
            ->displayIf(static function (MenuItem $item) {
                return !$item->hasChildren();
            })
            ->setCssClass('dropdown-item action-delete text-danger');

        $newMenuItemLinkUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->getNewMenuItemLinkController())
            ->set('crudAction', Crud::PAGE_NEW)
            ->generateUrl();

        $newLinkGlobalAction = Action::new('link', 'icon.text')
            ->linkToUrl($newMenuItemLinkUrl)->createAsGlobalAction();

        $newMenuItemIconUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->getNewMenuItemIconController())
            ->set('crudAction', Crud::PAGE_NEW)
            ->generateUrl();

        $newMenuItemGlobalAction = Action::new('icon', 'icon.menu')
            ->linkToUrl($newMenuItemIconUrl)->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_INDEX, $editLogo)
            ->add(Crud::PAGE_INDEX, $moveDownPosition)
            ->add(Crud::PAGE_INDEX, $moveUpPosition)
            ->add(Crud::PAGE_INDEX, $newLinkGlobalAction)
            ->add(Crud::PAGE_INDEX, $newMenuItemGlobalAction)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $deleteAction)
        ;
    }

    private function redirectToObjectCrudPage(bool $withFlash = true): RedirectResponse
    {
        if ($withFlash) {
            $this->addFlash('success', 'menu_item.update_successful');
        }
        $this->adminUrlGenerator
            ->unsetAll()
            ->setController($this->getMenuItemsControllerClass())
            ->setAction('index');

        return $this->redirect($this->adminUrlGenerator->generateUrl());
    }

    public function moveUpPosition(AdminContext $context): Response
    {
        /** @var MenuItem $item */
        $item = $context->getEntity()->getInstance();
        $item->setPosition($item->up());
        $this->menuItemRepository->save($item, true);

        return $this->redirectToObjectCrudPage();
    }

    public function moveDownPosition(AdminContext $context): Response
    {
        /** @var MenuItem $item */
        $item = $context->getEntity()->getInstance();
        $oldPosition = $item->getPosition();
        $item->setPosition($item->down());
        $this->menuItemRepository->save($item, true);
        $newPosition = $item->getPosition();

        return $this->redirectToObjectCrudPage($newPosition !== $oldPosition);
    }

    public static function getEntityFqcn(): string
    {
        return MenuItem::class;
    }

    public function createEntity(string $entityFqcn): MenuItem
    {
        /** @var MenuItem $menuItem */
        $menuItem = new $entityFqcn();
        $menuItem->setMenu($this->getMenu());

        return $menuItem;
    }

    public function getMenu(): Menu
    {
        return $this->menuRepository->getByCode($this->getCode());
    }

    /**
     * Only display menu items corresponding to a given menu.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $alias = $qb->getRootAliases()[0] ?? null;
        $qb->andWhere($alias.'.menu = :menu')
            ->setParameter('menu', $this->getMenu());

        return $qb;
    }

    /**
     * Return all possible fields.
     *
     * @return array<FieldInterface>
     */
    public function getFields(string $pageName): array
    {
        $linkTypeField = ChoiceField::new('linkType')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', LinkType::class)
            ->setChoices(LinkType::getAsArray())
        ;
        $nameField = TextField::new('name')
            ->setRequired(true);
        $linkField = TextField::new('link');

        $socialMediaTypeField = ChoiceField::new('mediaType')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', SocialMediaType::class)
            ->setChoices(SocialMediaType::getAsArray())
            ->setRequired(true);

        $parentField = AssociationField::new('parent')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $this->menuItemRepository->getLinksByCode($queryBuilder, $this->getCode());
            })
            ->setRequired(false);
        $menuField = AssociationField::new('menu');
        $positionField = IntegerField::new('position');
        $positionHumanField = IntegerField::new('positionHuman');

        return compact(
            'linkTypeField',
            'nameField',
            'linkField',
            'socialMediaTypeField',
            'parentField',
            'menuField',
            'positionField',
            'positionHumanField',
        );
    }

    public function configureFields(string $pageName): iterable
    {
        [
            'linkTypeField' => $linkTypeField,
            'nameField' => $nameField,
            'linkField' => $linkField,
            'socialMediaTypeField' => $socialMediaTypeField,
            'parentField' => $parentField,
            'positionHumanField' => $positionHumanField,
        ] = $this->getFields($pageName);

        if ($pageName === Crud::PAGE_EDIT) {
            /** @var ChoiceField $socialMediaTypeField */
            $socialMediaTypeField->setChoices(SocialMediaType::cases());

            /** @var MenuItem $item */
            $item = $this->getContext()?->getEntity()->getInstance();

            if ($item->getLinkType() === LinkType::LINK) {
                return [$nameField, $linkField, $parentField];
            }

            // social media
            return [$socialMediaTypeField, $linkField];
        }

        // show + list

        return [$nameField, $linkTypeField, $linkField, $parentField, $socialMediaTypeField, $positionHumanField];
    }
}
