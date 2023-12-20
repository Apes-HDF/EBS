<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\User;
use App\Form\Type\User\ChangeLoginFormType;
use App\Message\Command\User\ChangeLoginCommand;
use App\MessageBus\CommandBus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see ChangeLoginActionTest
 */
final class ChangeLoginAction extends AbstractController
{
    use SecurityTrait;
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-email',
        'fr' => MyAccountAction::BASE_URL_FR.'/mon-email',
    ], name: 'app_user_change_login')]
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ChangeLoginFormType::class, $user)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');
            /** @var string $email */
            $email = $form->get('email')->getData();
            $command = new ChangeLoginCommand($user->getId(), $email);
            $this->commandBus->dispatch($command);

            return $this->redirectToRoute(MyAccountAction::ROUTE);
        }

        // In case of error, we must reload the original email
        $this->entityManager->refresh($user);

        return $this->render('pages/user/account/change_login.html.twig', compact('form'));
    }
}
