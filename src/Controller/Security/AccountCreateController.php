<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\User;
use App\Exception\UserConfirmationTokenExpiredException;
use App\Exception\UserNotFoundException;
use App\Form\Type\Security\AccountCreateStep1FormType;
use App\Form\Type\Security\AccountCreateStep2FormType;
use App\Message\Command\Security\AccountCreateStep1Command;
use App\Message\Command\Security\AccountCreateStep2Command;
use App\Message\Command\Security\AccountCreateStep2RefreshCommand;
use App\Message\Query\Security\GetUserByTokenQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use App\MessageHandler\Command\Security\AccountCreateStep1CommandHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @see AccountCreateActionStep1Test
 * @see AccountCreateActionStep2UserTest
 */
final class AccountCreateController extends AbstractController
{
    use FlashTrait;
    use i18nTrait;

    private string $i18nPrefix;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
        private readonly Security $security,
    ) {
        $this->i18nPrefix = $this->getI18nPrefix();
    }

    /**
     * @see AccountCreateStep1CommandHandler
     */
    #[Route(path: [
        'en' => '/{_locale}/account/create-my-account',
        'fr' => '/{_locale}/compte/creer-mon-compte',
    ], name: 'security_account_create_step1')]
    public function createStep1(Request $request, string $_route): Response
    {
        $form = $this->createForm(AccountCreateStep1FormType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $newUser */
            $newUser = $form->getData();
            $this->commandBus->dispatch(new AccountCreateStep1Command($newUser));
            $this->addFlashSuccess($this->i18nPrefix.'.step1.flash.success');

            return $this->redirectToRoute($_route);
        }

        return $this->render('pages/register/step1.html.twig', compact('form'));
    }

    /**
     * @see AccountCreateStep2CommandHandler
     */
    #[Route(path: [
        'en' => '/{_locale}/account/create-my-account-step2/{token}',
        'fr' => '/{_locale}/compte/creer-mon-compte-etape-2/{token}',
    ], name: 'security_account_create_step2')]
    public function createStep2(Request $request, string $token): Response
    {
        try {
            /** @var User $user */
            $user = $this->queryBus->query(new GetUserByTokenQuery($token));
        } catch (HandlerFailedException $e) {
            /** @var \Exception $exception */
            $exception = $e->getPrevious();
            switch ($exception::class) {
                case UserNotFoundException::class:
                    $this->addFlashWarning($this->i18nPrefix.'.step2.user_not_found.warning');
                    break;

                case UserConfirmationTokenExpiredException::class:
                    // send a new confirmation email with a new token
                    $this->commandBus->dispatch(new AccountCreateStep2RefreshCommand($exception->id));
                    $this->addFlashWarning($this->i18nPrefix.'.step2.user_confirmation_token_expired.warning');
                    break;
            }

            return $this->redirectToRoute('app_login');
        }

        // nominal case: user found and token not expired
        $form = $this->createForm(AccountCreateStep2FormType::class, $user->setStep2Defaults())->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $this->commandBus->dispatch(new AccountCreateStep2Command($user));
            $this->security->login($user); // auto-log the user

            // If user has pending invitations then redirect them to the first group
            // found without doing the confirmation stuff, it must be done on the
            // page group.
            $group = $user->getMyGroupsAsInvited()->first();
            if ($group !== false) {
                $this->addFlashSuccess($this->i18nPrefix.'.step2.with_invitation.flash.success');

                return $this->redirectToRoute('app_group_show_logged', $group->getRoutingParameters());
            }

            // otherwise go to the address form
            $this->addFlashSuccess($this->i18nPrefix.'.step2.flash.success');

            return $this->redirectToRoute(MyAccountAction::ROUTE);
        }

        return $this->render('pages/register/step2.html.twig', compact('form', 'user'));
    }
}
