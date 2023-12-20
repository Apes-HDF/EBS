<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see DeleteUserAvatarActionTest
 */
#[IsGranted(User::ROLE_USER)]
final class DeleteUserAvatarAction extends AbstractController
{
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly UserManager $userManager,
    ) {
    }

    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/user/{userId}/delete-avatar',
        name: 'app_user_delete_avatar',
        requirements: [
            'userId' => Requirement::UUID_V6,
        ]
    )]
    public function __invoke(#[CurrentUser] User $user): Response
    {
        $this->userManager->deleteAvatar($user);
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

        return $this->redirectToRoute('app_user_edit_profile');
    }
}
