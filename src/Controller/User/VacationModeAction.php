<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Entity\User;
use App\Message\Command\User\ChangeVacationModeCommand;
use App\MessageBus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class VacationModeAction extends AbstractController
{
    use SecurityTrait;
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[IsGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/vacation-mode',
        'fr' => MyAccountAction::BASE_URL_FR.'/mode-vacances',
    ], name: 'user_toggle_vacation_mode')]
    public function __invoke(#[CurrentUser] User $user, string $_route): Response
    {
        $command = new ChangeVacationModeCommand($user->getId());
        $this->commandBus->dispatch($command);
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

        return $this->redirectToRoute(MyAccountAction::ROUTE);
    }
}
