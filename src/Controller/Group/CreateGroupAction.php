<?php

declare(strict_types=1);

namespace App\Controller\Group;

use App\Controller\Admin\GroupCrudController;
use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\RequestTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Form\Type\Group\CreateGroupFormType;
use App\MessageBus\QueryBus;
use App\Repository\ConfigurationRepository;
use App\Repository\GroupRepository;
use App\Repository\UserGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see CreateGroupActionTest
 */
#[Route(name: 'app_group_')]
final class CreateGroupAction extends AbstractController
{
    use RequestTrait;
    use i18nTrait;
    use FlashTrait;
    use GroupTrait;

    public const MAX_ELEMENT_BY_PAGE = 20;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly GroupRepository $groupRepository,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly UserGroupRepository $userGroupRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly Security $security,
        private readonly EntityManagerInterface $doctrine,
    ) {
    }

    #[isGranted(User::ROLE_USER)]
    #[Route([
        'en' => MyAccountAction::BASE_URL_EN.'/groups/create-my-group',
        'fr' => MyAccountAction::BASE_URL_FR.'/groupes/creer-mon-groupe',
    ],
        name: 'create',
    )]
    public function createGroup(Request $request, #[CurrentUser] User $user): Response
    {
        $configuration = $this->configurationRepository->getInstanceConfigurationOrCreate();
        if (!$configuration->isGroupsCreationForAll()) {
            throw $this->createAccessDeniedException('Cannot create group with current settings.');
        }

        $newGroup = (new Group())->setInvitationByAdmin(true);
        $form = $this->createForm(CreateGroupFormType::class, $newGroup)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupRepository->save($newGroup, true);
            $newAdminGroup = UserGroup::newUserGroup($user, $newGroup);
            $this->userGroupRepository->save($newAdminGroup, true);

            // force login to refresh the roles so the user can access its group
            // in the group admin interface
            $this->doctrine->refresh($user);
            $this->security->login($user);

            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(GroupCrudController::class)
                    ->setEntityId($newGroup->getId())
                    ->set('crudAction', Crud::PAGE_EDIT)
                    ->generateUrl()
            );
        }

        return $this->render('pages/group/create.html.twig', compact('form'));
    }
}
