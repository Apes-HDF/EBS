<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\FieldTrait;
use App\EasyAdmin\Filter\EnumFilter;
use App\EasyAdmin\Filter\UuidFilter;
use App\EasyAdmin\Form\Type\LoanStatusType;
use App\Entity\ServiceRequest;
use App\Enum\ServiceRequest\ServiceRequestStatus;
use App\Repository\ConfigurationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ServiceRequestCrudController extends AbstractCrudController implements AdminSecuredCrudControllerInterface
{
    use FieldTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Loan')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (ServiceRequest $serviceRequest) => $this->translator->trans(
                'loan.title',
                [
                    '%date%' => $serviceRequest->getStartAt()->format($this->translator->trans('format.date', [], 'date')),
                    '%lender%' => $serviceRequest->getOwner()->getDisplayName(),
                    '%borrower%' => $serviceRequest->getRecipient()->getDisplayName(),
                ],
                DashboardController::DOMAIN)
            )
            ->setEntityLabelInPlural('loans')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(UuidFilter::new('id'))
            ->add('owner')
            ->add('product')
            ->add('recipient')
            ->add(EnumFilter::new('status', LoanStatusType::class))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $conversation = Action::new('conversation')
            ->linkToCrudAction('conversation')
            ->setIcon('fas fa-comment-dots')
            ->displayIf(fn () => $this->configurationRepository->getInstanceConfiguration()?->isConversationAdminAccessible())
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_DETAIL, $conversation)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setCssClass('btn btn-sm btn-primary')
                    ->setIcon('fa fa-search');
            })
        ;
    }

    public static function getEntityFqcn(): string
    {
        return ServiceRequest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $ownerField = AssociationField::new('owner');
        $recipientField = AssociationField::new('recipient');
        $productField = AssociationField::new('product');
        $productTypeField = AssociationField::new('product')
            ->setTemplatePath('admin/loan/product_type_field.html.twig')
            ->setLabel('type');
        $statusField = ChoiceField::new('status')
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', ServiceRequest::class)
            ->setChoices(ServiceRequestStatus::getAsArray());
        $messageCountField = IntegerField::new('messagesCount');

        $startAt = DateTimeField::new('startAt');
        $endAt = DateTimeField::new('endAt');
        $createdAt = DateTimeField::new('createdAt');

        if ($pageName === Crud::PAGE_INDEX) {
            return [$ownerField, $productTypeField, $productField, $recipientField, $statusField, $startAt, $endAt, $messageCountField, $createdAt];
        }

        //  no new and edit page for this crud

        // show
        $ownerField = AssociationField::new('owner')
            ->setTemplatePath('admin/loan/user_field.html.twig')
            ->setLabel('');
        $recipientField = AssociationField::new('recipient')
            ->setTemplatePath('admin/loan/user_field.html.twig')
            ->setLabel('');
        $productField = AssociationField::new('product')
            ->setTemplatePath('admin/loan/product_field.html.twig')
            ->setLabel('');

        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $this->getContext()?->getEntity()->getInstance();
        $productPanel = FormField::addPanel($serviceRequest->getProduct()->getType()->value, 'fa-solid fa-box');
        $serviceRequestInformationPanel = FormField::addPanel('panel.loan_information', 'fas fa-info-circle');
        $ownerPanel = FormField::addPanel('panel.lender', 'fa-solid fa-user');
        $recipientPanel = FormField::addPanel('panel.borrower', 'fa-solid fa-user');

        return [
            $serviceRequestInformationPanel,
            $startAt,
            $endAt,
            $statusField,
            $createdAt,

            $ownerPanel,
            $ownerField,

            $recipientPanel,
            $recipientField,

            $productPanel,
            $productField,
        ];
    }

    /**
     * Entity is accesible thanks to the EA AdminContext.
     */
    public function conversation(): Response
    {
        if (!$this->configurationRepository->getInstanceConfigurationOrCreate()->isConversationAdminAccessible()) {
            throw new AccessDeniedHttpException();
        }

        return $this->render('admin/service_request/conversation.html.twig');
    }
}
