<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Dev\DevToolsController;
use App\Controller\User\MyAccountAction;
use App\Entity\Group;
use App\Entity\Page;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\User\UserType;
use App\Repository\GroupRepository;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserRepository;
use App\Security\Checker\AuthorizationChecker;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * All /admin routes are protected at the security.yaml level.
 */
#[Security("is_granted('".User::ROLE_ADMIN."') or is_granted('".User::ROLE_GROUP_ADMIN."')")]
final class DashboardController extends AbstractDashboardController
{
    public const DOMAIN = 'admin';

    // This is to have the menu entry outlined when on a page.
    // Check if we can do this a cleaner way.
    public const MENU_INDEX = [
        // root
        AdministratorCrudController::class => 1,

        // content
        MenuItemCrudController::class => 4,
        MenuItemFooterCrudController::class => 5,
        CategoryObjectCrudController::class => 6, // +1

        // usage
        UserCrudController::class => 8,
        PlaceCrudController::class => 9,

        // Group (+1)
        ObjectCrudController::class => 11,
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly GroupRepository $groupRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly UserRepository $userRepository,
        private readonly ServiceRequestRepository $requestRepository
    ) {
    }

    /**
     * @see https://symfony.com/bundles/EasyAdminBundle/current/dashboards.html#/translation
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->translator->trans('dashboard.title', [], self::DOMAIN))
            ->setTranslationDomain('admin')
            ->generateRelativeUrls()
            ->setLocales([
                'en' => '🇬🇧 Anglais',
                'fr' => '🇫🇷 Français',
            ])
        ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        if (!$this->authorizationChecker->isAdmin()) {
            $groupUrl = $this->adminUrlGenerator
                ->setController(GroupCrudController::class)
                ->generateUrl();

            return $this->redirect($groupUrl);
        }

        return $this->render('admin/dashboard.html.twig', [
            'group_count' => $this->groupRepository->count([]),
            'user_count' => $this->userRepository->getUserCountByType(UserType::USER),
            'place_count' => $this->userRepository->getUserCountByType(UserType::PLACE),
            'month_users_count' => $this->userRepository->getNewUsersOfMonthByType(UserType::USER),
            'month_places_count' => $this->userRepository->getNewUsersOfMonthByType(UserType::PLACE),
            'month_requests_count' => $this->requestRepository->getNewServiceRequestsOfMonth(),
        ]);
    }

    public function configureMenuItems(): iterable
    {
        /** @var User $user */
        $user = $this->getUser();

        // —————————————————————————————————————————————————————————————————————
        yield MenuItem::linkToDashboard('menu.dashboard', 'fa fa-home')->setPermission(User::ROLE_ADMIN);

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(AdministratorCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[AdministratorCrudController::class]);

        yield MenuItem::linkToUrl('menu.administrators', 'fas fa-user-plus', $url->generateUrl())->setPermission(User::ROLE_ADMIN);
        yield MenuItem::linkToRoute('menu.parameters', 'fas fa-cog', ParametersController::ROUTE_NAME)->setPermission(User::ROLE_ADMIN)->setPermission(User::ROLE_ADMIN);

        // —————————————————————————————————————————————————————————————————————
        yield MenuItem::section('menu.content');

        $menuConfigUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(MenuItemCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[MenuItemCrudController::class])
            ->generateUrl();
        $footerConfigUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(MenuItemFooterCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[MenuItemFooterCrudController::class])
            ->generateUrl();

        $categoryObjectUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(CategoryObjectCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[CategoryObjectCrudController::class])
            ->set('submenuIndex', 0)
            ->generateUrl();
        $categoryServiceUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(CategoryServiceCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[CategoryObjectCrudController::class])
            ->set('submenuIndex', 1)
            ->generateUrl();

        yield MenuItem::linkToUrl('menu.config_menu', 'fa-solid fa-bars', $menuConfigUrl)->setPermission(User::ROLE_ADMIN);
        yield MenuItem::linkToUrl('menu.config_footer', 'fas fa-ellipsis-h', $footerConfigUrl)->setPermission(User::ROLE_ADMIN);

        yield MenuItem::linkToCrud('menu.pages', 'fas fa-hat-wizard', Page::class)->setPermission(User::ROLE_ADMIN);

        yield MenuItem::subMenu('menu.categories', 'fa-solid fa-folder')->setSubItems([
             MenuItem::linkToUrl('menu.objects', 'fa-solid fa-box', $categoryObjectUrl)->setPermission(User::ROLE_ADMIN),
             MenuItem::linkToUrl('menu.services', 'fa-regular fa-handshake', $categoryServiceUrl)->setPermission(User::ROLE_ADMIN),
        ])->setPermission(User::ROLE_ADMIN);

        // —————————————————————————————————————————————————————————————————————
        yield MenuItem::section('menu.usage')->setPermission(User::ROLE_ADMIN);

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(UserCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[UserCrudController::class])
        ;
        yield MenuItem::linkToUrl('menu.users', 'fas fa-user', $url->generateUrl())->setPermission(User::ROLE_ADMIN);
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(PlaceCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[PlaceCrudController::class])
        ;

        yield MenuItem::linkToUrl('menu.places', 'fas fa-location-dot', $url->generateUrl())->setPermission(User::ROLE_ADMIN);

        yield MenuItem::subMenu('menu.groups', 'fas fa-users')->setSubItems([
            MenuItem::linkToCrud('menu.groups', 'fas fa-users', Group::class)->setPermission(User::ROLE_GROUP_ADMIN),
            MenuItem::linkToCrud('menu.members', 'fas fa-user-friends', UserGroup::class)->setPermission(User::ROLE_GROUP_ADMIN),
        ])->setPermission(User::ROLE_GROUP_ADMIN);

        $objectUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ObjectCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[ObjectCrudController::class])
            ->set('submenuIndex', 0)
            ->generateUrl();

        $serviceUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ServiceCrudController::class)
            ->set('crudAction', Crud::PAGE_INDEX)
            ->set('menuIndex', self::MENU_INDEX[ObjectCrudController::class])
            ->set('submenuIndex', 1)
            ->generateUrl();

        yield MenuItem::subMenu('menu.articles', 'fa-solid fa-box')->setSubItems([
            MenuItem::linkToUrl('menu.objects', 'fa-solid fa-box', $objectUrl)->setPermission(User::ROLE_ADMIN),
            MenuItem::linkToUrl('menu.services', 'fa-regular fa-handshake', $serviceUrl)->setPermission(User::ROLE_ADMIN),
        ])->setPermission(User::ROLE_ADMIN);

        yield MenuItem::linkToCrud('menu.loans', 'fas fa-link', ServiceRequest::class)->setPermission(User::ROLE_ADMIN);

        // —————————————————————————————————————————————————————————————————————
        if ($user->isDevAccount()) {
            yield MenuItem::section('menu.devtools')->setPermission(User::ROLE_ADMIN);
            yield MenuItem::linkToRoute('menu.dev_tools', 'fas fa-wrench', DevToolsController::ROUTE_NAME)->setPermission(User::ROLE_ADMIN);
        }

        // —————————————————————————————————————————————————————————————————————
        yield MenuItem::section('menu.public');
        yield MenuItem::linkToUrl('menu.home', 'fa fa-home', '/')->setLinkTarget('_blank');
        yield MenuItem::linkToUrl('menu.user', 'fa fa-user', $this->generateUrl(MyAccountAction::ROUTE))->setLinkTarget('_blank');
    }
}
