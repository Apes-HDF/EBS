<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Exception\UserPendingEmailAlreadyUsedException;
use App\Exception\UserPendingEmailTokenExpiredException;
use App\Message\Command\User\ConfirmChangeLoginCommand;
use App\MessageBus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Applies the new email requested with ChangeLoginAction. The user must be connected
 * to the account that made the request, so the owner of the new address can't
 * confirm a request made by someone else.
 *
 * @see ConfirmChangeLoginActionTest
 */
final class ConfirmChangeLoginAction extends AbstractController
{
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: [
        'en' => '/{_locale}/account/confirm-my-email/{token}',
        'fr' => '/{_locale}/compte/confirmer-mon-email/{token}',
    ], name: 'app_user_change_login_confirm')]
    public function __invoke(string $token, #[CurrentUser] User $user): Response
    {
        $i18nPrefix = $this->getI18nPrefix();
        try {
            $this->commandBus->dispatch(new ConfirmChangeLoginCommand($user->getId(), $token));
        } catch (HandlerFailedException $e) {
            /** @var \Exception $exception */
            $exception = $e->getPrevious();
            switch ($exception::class) {
                case UserNotFoundException::class:
                    $this->addFlashWarning($i18nPrefix.'.flash.token_invalid');
                    break;

                case UserPendingEmailTokenExpiredException::class:
                    $this->addFlashWarning($i18nPrefix.'.flash.token_expired');
                    break;

                case UserPendingEmailAlreadyUsedException::class:
                    $this->addFlashWarning($i18nPrefix.'.flash.email_already_used');
                    break;

                default:
                    throw $e;
            }

            return $this->redirectToRoute(MyAccountAction::ROUTE);
        }

        // the user of the security token is the entity updated above: it is stored in
        // the session with its new email, so the user stays logged in
        $this->addFlashSuccess($i18nPrefix.'.flash.success');

        return $this->redirectToRoute(MyAccountAction::ROUTE);
    }
}
