<?php

declare(strict_types=1);

namespace App\Controller\User\Group;

use App\Controller\FlashTrait;
use App\Controller\Group\GroupTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Group;
use App\Entity\User;
use App\Message\Command\User\Group\AcceptGroupInvitationCommand;
use App\Message\Command\User\Group\JoinGroupCommand;
use App\Message\Command\User\Group\QuitGroupCommand;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see UserGroupControllerTest
 */
#[isGranted(User::ROLE_USER)]
final class UserGroupController extends AbstractController
{
    use SecurityTrait;
    use FlashTrait;
    use i18nTrait;
    use GroupTrait;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus
    ) {
    }

    private function getGroupAndCheckToken(Request $request, string $id, string $tokenId): Group
    {
        $group = $this->getGroup($id);

        /** @var ?string $token */
        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw new UnprocessableEntityHttpException('Invalid CSRF token');
        }

        return $group;
    }

    private function redirectToGroup(Group $group): RedirectResponse
    {
        return $this->redirectToRoute('app_group_show', $group->getRoutingParameters());
    }

    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/groups/{id}/join',
        name: 'app_user_group_join',
        requirements: ['id' => Requirement::UUID_V6],
        methods: ['POST'],
    )]
    public function join(Request $request, string $id, #[CurrentUser] User $user): Response
    {
        $group = $this->getGroupAndCheckToken($request, $id, 'join_group');
        $command = new JoinGroupCommand($group->getId(), $user->getId());
        $this->commandBus->dispatch($command);
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

        return $this->redirectToGroup($group);
    }

    /**
     * @see UserGroupAcceptInvitationActionTest
     */
    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/groups/{id}/acceptInvitation',
        name: 'app_user_group_accept_invitation',
        requirements: ['id' => Requirement::UUID_V6],
        methods: ['POST'],
    )]
    public function acceptInvitation(Request $request, string $id, #[CurrentUser] User $user): Response
    {
        $group = $this->getGroupAndCheckToken($request, $id, 'accept_invitation');
        $command = new AcceptGroupInvitationCommand($group->getId(), $user->getId());
        $this->commandBus->dispatch($command);
        $this->addFlashSuccess($this->getI18nPrefix().'.accept_invitation.flash.success');

        return $this->redirectToGroup($group);
    }

    /**
     * @see UserGroupQuitGroupActionTest
     */
    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/groups/{id}/quitGroup',
        name: 'app_user_group_quit_group',
        requirements: ['id' => Requirement::UUID_V6],
        methods: ['POST'],
    )]
    public function quitGroup(Request $request, string $id, #[CurrentUser] User $user): Response
    {
        $group = $this->getGroupAndCheckToken($request, $id, 'quit_group');
        $type = $request->request->getAlpha('type');
        $command = new QuitGroupCommand($group->getId(), $user->getId(), $type);
        $this->commandBus->dispatch($command);
        $this->addFlashSuccess($this->getI18nPrefix().'.quit_group.flash.success');

        return $this->redirectToGroup($group);
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-groups',
        'fr' => MyAccountAction::BASE_URL_FR.'/mes-groupes',
    ], name: 'app_user_groups'
    )]
    public function list(): Response
    {
        return $this->render('pages/user/group/list.html.twig');
    }
}
