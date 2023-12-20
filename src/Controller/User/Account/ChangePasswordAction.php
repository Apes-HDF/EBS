<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Form\Type\User\ChangePasswordFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangePasswordAction extends AbstractController
{
    use i18nTrait;
    use FlashTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
    ) {
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-password',
        'fr' => MyAccountAction::BASE_URL_FR.'/mon-mot-de-passe',
    ], name: 'app_user_change_password')]
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plaintextPassword */
            $plaintextPassword = $form->get('plainPassword')->getData();

            $this->userManager->updatePassword($user->setPlainPassword($plaintextPassword));
            $this->userRepository->save($user, true);

            $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

            return $this->redirectToRoute('app_user_my_account');
        }

        return $this->render('pages/user/account/change_password.html.twig', compact('form'));
    }
}
