<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\EasyAdmin\Form\Type\OfferTypeType;
use App\Entity\PlatformOffer;
use App\Enum\OfferType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

final class PlatformOfferCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FlashTrait;
    use FieldTrait;
    use i18nTrait;

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('platform_offers')
            ->setSearchFields(['name'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add(EnumFilter::new('type', OfferTypeType::class))
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
        return PlatformOffer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $idFIeld = IdField::new('id')
            ->setLabel('id')
            ->hideOnForm();

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
            return [$nameField, $typeField, $priceField, $activeField, $createdAtField, $updatedAtField];
        }

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $typeField->setChoices(OfferType::cases());

            return [
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
