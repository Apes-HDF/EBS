<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\FieldTrait;
use App\Entity\Page;
use App\Repository\PageRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class PageCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('pages')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC'])
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
    }

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('enabled')
            ->add('home')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $linkToFrontPage = Action::new('link', 'page.action.link')
            ->linkToCrudAction('redirectToFrontPage')
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_DETAIL, $linkToFrontPage)
        ;
    }

    public function redirectToFrontPage(): RedirectResponse
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->adminUrlGenerator->get('entityId'));

        return $this->redirectToRoute('app_cms_page', ['slug' => $page->getSlug()]);
    }

    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $idFIeld = IdField::new('id')->setLabel('id');
        $nameField = TextField::new('name');
        $contentField = TextEditorField::new('content')->setFormType(CKEditorType::class)->addCssFiles('ckeditor.css');
        $slugField = TextField::new('slug');
        $homeField = $this->getSimpleBooleanField('home');
        $enabledField = $this->getSimpleBooleanField('enabled');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        if ($pageName === Crud::PAGE_INDEX) {
            return [$nameField, $slugField, $homeField, $enabledField, $createdAt, $updatedAt];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            return [$nameField, $homeField, $enabledField, $contentField];
        }

        // show
        $panels = $this->getPanels();

        return [
            $panels['information'],
            $nameField,
            $homeField,
            $enabledField,
            $contentField,

            $panels['tech_information'],
            $idFIeld,
            $slugField,
            $createdAt,
            $updatedAt,
        ];
    }
}
