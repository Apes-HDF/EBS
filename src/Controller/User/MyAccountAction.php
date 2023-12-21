<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\ConfigurationRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Connected user homepage.
 *
 * @see security.yaml
 */
final class MyAccountAction extends AbstractController
{
    // to synchronize with security.yaml
    public const BASE_URL_EN = '/en/my-account';
    public const BASE_URL_FR = '/fr/mon-compte';

    public const ROUTE = 'app_user_my_account';

    public function __construct(
        public MessageRepository $messageRepository,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    #[isGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => self::BASE_URL_EN,
        'fr' => self::BASE_URL_FR,
    ], name: self::ROUTE)]
    public function __invoke(#[CurrentUser] User $user): Response
    {
        $userHasNewLendingMessage = $this->messageRepository->userHasNewMessage($user, true);
        $userHasNewLoanMessage = $this->messageRepository->userHasNewMessage($user, false);
        $configuration = $this->configurationRepository->getInstanceConfigurationOrCreate();

        // we can create a group if the settings for all is activated or an administrator
        $canCreateGroup = $configuration->isGroupsCreationForAll() || $user->isAdmin();
        $contactEmail = $configuration->getContactEmail();

        $servicesConfig = $this->configurationRepository->getServicesParameter();

        return $this->render('pages/account/index.html.twig', compact('userHasNewLoanMessage', 'userHasNewLendingMessage', 'canCreateGroup', 'contactEmail', 'servicesConfig'));
    }
}
