<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\FlashTrait;
use App\Entity\User;
use App\Exception\UserLostPasswordTokenExpiredException;
use App\Exception\UserNotFoundException;
use App\Form\Type\Security\ResetPasswordFormType;
use App\Message\Command\Security\ResetPasswordCommand;
use App\Message\Query\Security\ResetPasswordQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

final class ResetPasswordAction extends AbstractController
{
    use FlashTrait;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route(path: [
        'en' => '/{_locale}/account/password-reset/{token}',
        'fr' => '/{_locale}/compte/reinitialisation-mot-de-passe/{token}',
    ], name: 'security_reset_password')]
    public function __invoke(Request $request, string $token, string $_route): Response
    {
        try {
            /** @var User $user */
            $user = $this->queryBus->query(new ResetPasswordQuery($token));
        } catch (HandlerFailedException $e) {
            /** @var \Exception $exception */
            $exception = $e->getPrevious();

            if ($exception::class === UserNotFoundException::class) {
                $this->addFlashWarning('reset_password.user_not_found.exception');
            }

            // token expired, the user must renew its request
            if ($exception::class === UserLostPasswordTokenExpiredException::class) {
                $this->addFlashWarning('reset_password.user_lostpassword_token_expired.exception');
            }

            return $this->redirectToRoute('security_lost_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ResetPasswordCommand $resetPasswordCommand */
            $resetPasswordCommand = $form->getData();
            $resetPasswordCommand->id = $user->getId();
            $this->commandBus->dispatch($resetPasswordCommand);
            $this->addFlashSuccess('reset_password.form.success');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('pages/password/reset.html.twig', compact('form'));
    }
}
