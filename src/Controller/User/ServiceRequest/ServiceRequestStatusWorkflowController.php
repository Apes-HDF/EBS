<?php

declare(strict_types=1);

namespace App\Controller\User\ServiceRequest;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Enum\ServiceRequest\ServiceRequestStatusTransition;
use App\Form\Type\ServiceRequest\ModifyServiceRequestType;
use App\MessageBus\QueryBus;
use App\Workflow\ServiceRequestStatusWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function Symfony\Component\String\u;

/**
 * @see ServiceRequestStatusWorkflowControllerTest
 */
#[isGranted(User::ROLE_USER)]
class ServiceRequestStatusWorkflowController extends AbstractController
{
    use FlashTrait;
    use SecurityTrait;
    use ServiceRequestTrait;
    use i18nTrait;

    final public const ROUTE = 'app_user_service_request_transition';

    /**
     * We don't use the command bus here because the process is one line long, so
     * there is nothing to factorize. Let's stay.
     */
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly ServiceRequestStatusWorkflow $serviceRequestStatusWorkflow,
        private readonly EntityManagerInterface $doctrine,
    ) {
    }

    /**
     * This is the general action for all generic transitions.
     */
    #[Route(
        path: MyAccountAction::BASE_URL_FR.'/service/{id}/transition/{transition}',
        name: self::ROUTE,
        requirements: [
            'id' => Requirement::UUID_V6,
            'transition' => new EnumRequirement(ServiceRequestStatusTransition::class),
        ],
        methods: ['POST'],
    )]
    public function apply(Request $request, string $id, ServiceRequestStatusTransition $transition): Response
    {
        /** @var ?string $submittedToken */
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('transition', $submittedToken)) {
            throw new UnprocessableEntityHttpException('Invalid CSRF token');
        }

        $serviceRequest = $this->getMyServiceRequest($id);

        return $this->applyAndRedirect($id, $serviceRequest, $transition);
    }

    /**
     * Specific controller for transitions having to pass extra information.
     */
    #[Route(
        path: MyAccountAction::BASE_URL_FR.'/service/{id}/transition/modify/{transition}',
        name: self::ROUTE.'_modify',
        requirements: [
            'id' => Requirement::UUID_V6,
            'transition' => new EnumRequirement([
                ServiceRequestStatusTransition::MODIFY_OWNER,
                ServiceRequestStatusTransition::MODIFY_RECIPIENT,
            ]),
        ],
        methods: ['POST']
    )]
    public function applyModifyOwner(Request $request, string $id, ServiceRequestStatusTransition $transition): Response
    {
        $serviceRequest = $this->getMyServiceRequest($id);
        $form = $this->createForm(ModifyServiceRequestType::class, $serviceRequest)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->flush(); // entity is modified at form submission

            return $this->applyAndRedirect($id, $serviceRequest, $transition);
        }

        return $this->forward(ConversationController::class, compact('id'));
    }

    private function applyAndRedirect(string $id, ServiceRequest $serviceRequest, ServiceRequestStatusTransition $transition): Response
    {
        try {
            $this->serviceRequestStatusWorkflow->apply($serviceRequest, $transition);
        } catch (\LogicException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
        $this->doctrine->flush();
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.'.$serviceRequest->getProduct()->getType()->value.'.'.u($transition->value)->snake());

        return $this->redirectToRoute(ConversationController::ROUTE, compact('id'));
    }
}
