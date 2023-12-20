<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\User\MyUsersFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\Entity\Payment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @see PaymentCrudControllerTest
 */
final class PaymentCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('payments')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add(UuidFilter::new('id'))
            ->add(MyUsersFilter::new('user'))
            ->add('totalAmount')
        ;

        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
        ;
    }

    public static function getEntityFqcn(): string
    {
        return Payment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $idFIeld = IdField::new('id');
        $userField = AssociationField::new('user');
        $clientEmailField = TextField::new('clientEmail');
        $clientIdField = TextField::new('clientId');
        $numberFIeld = TextField::new('number');
        $descriptionField = TextField::new('description');
        $totalAmountField = MoneyField::new('totalAmount')
            ->setCurrencyPropertyPath('currencyCode');
        $paidField = BooleanField::new('paid')
            ->setTemplatePath('easy_admin/field/boolean.html.twig');
        $statusField = TextField::new('status');
        $methodField = TextField::new('method');
        $detailField = ArrayField::new('details')
            ->setTemplatePath('easy_admin/field/json.html.twig');

        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');

        if ($pageName === Crud::PAGE_INDEX) {
            return [$userField, $methodField, $totalAmountField, $paidField, $statusField, $descriptionField, $createdAt];
        }

        // show
        $panels = $this->getPanels();

        return [
            $panels['information'],
            $userField,
            $clientEmailField,
            $methodField,
            $totalAmountField,
            $paidField,
            $statusField,
            $descriptionField,

            $panels['tech_information'],
            $idFIeld,
            $numberFIeld,
            $clientIdField,
            $detailField,
            $createdAt,
            $updatedAt,
        ];
    }
}
