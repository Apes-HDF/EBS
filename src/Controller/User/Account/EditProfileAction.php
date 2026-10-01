<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Form\Type\User\EditProfileFormType;
use App\Repository\UserRepository;
use App\Tests\Functional\Controller\User\Account\EditProfileActionTest;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see EditProfileActionTest
 */
final class EditProfileAction extends AbstractController
{
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/edit',
        'fr' => MyAccountAction::BASE_URL_FR.'/editer',
    ], name: 'app_user_edit_profile')]
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(EditProfileFormType::class, $user)->handleRequest($request);
        if ($form->isSubmitted()) {
            $phone = $form->get('phone')->getData() ?? '';

            if (!$phone instanceof PhoneNumber || $phone->getNationalNumber() === '') {
                $form->get('phone')->addError(
                    new FormError($this->translator->trans('account_create.phone.empty.error', [], 'validators'))
                );
            }

            if ($phone instanceof PhoneNumber && \strlen($phone->getNationalNumber() ?? '') < 8) {
                $form->get('phone')->addError(
                    new FormError($this->translator->trans('account_create.phone.short.error', [], 'validators'))
                );
            }

            if ($form->isValid()) {
                /** @var UploadedFile|null $avatar */
                $avatar = $form->get('avatar')->getData();
                $this->userManager->upload($avatar, $user);
                $this->userRepository->save($user, true);
                $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

                return $this->redirectToRoute(MyAccountAction::ROUTE);
            }
        }

        // In case of error, we must reload the original firstname (to display it in navbar)
        $this->entityManager->refresh($user);

        return $this->render('pages/user/account/edit_profile.html.twig', compact('form'));
    }
}
