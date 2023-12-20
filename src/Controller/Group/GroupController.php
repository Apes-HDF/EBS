<?php

declare(strict_types=1);

namespace App\Controller\Group;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\RequestTrait;
use App\Doctrine\Manager\ProductManager;
use App\Entity\Group;
use App\Entity\User;
use App\Form\Type\Product\GroupSelectFormType;
use App\Message\Query\Group\GetGroupMembersQuery;
use App\Message\Query\Group\GetGroupsQuery;
use App\MessageBus\QueryBus;
use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * @see GroupControllerTest
 */
#[Route(name: 'app_group_')]
final class GroupController extends AbstractController
{
    use RequestTrait;
    use i18nTrait;
    use FlashTrait;
    use GroupTrait;

    public const MAX_ELEMENT_BY_PAGE = 20;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly PaginatorInterface $paginator,
        private readonly ProductManager $productManager,
    ) {
    }

    #[Route([
        'en' => '/{_locale}/groups',
        'fr' => '/{_locale}/groupes',
    ],
        name: 'list'
    )]
    public function list(Request $request, #[CurrentUser] ?User $user): Response
    {
        $page = $this->getPage($request);
        $form = $this->createForm(GroupSelectFormType::class)->handleRequest($request);
        $groupName = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $groupName */
            $groupName = $form->get('q')->getData();
        }

        /** @var Query $query */
        $query = $this->queryBus->query(new GetGroupsQuery($user, $groupName));
        $pagination = $this->paginator->paginate($query, $page, self::MAX_ELEMENT_BY_PAGE);

        return $this->render('pages/group/list.html.twig', compact('pagination', 'form'));
    }

    /**
     * The slug is only for SEO.
     */
    #[Route([
        'en' => '/{_locale}/groups/{slug}/{id}',
        'fr' => '/{_locale}/groupes/{slug}/{id}',
    ],
        name: 'show',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6]
    )]
    public function show(string $id, #[CurrentUser] ?User $user): Response
    {
        $group = $this->getGroup($id);
        $showQuitGroupChoices = $this->getShowQuitGroupsChoices($group, $user);

        return $this->render('pages/group/show.html.twig', compact('group', 'showQuitGroupChoices'));
    }

    /**
     * This is the same route as show but it requires to be logged. It is useful to
     * redirect the user to this page if he isn't logged, so he can accept a pending
     * invitation for example.
     */
    #[IsGranted(User::ROLE_USER)]
    #[Route([
        'en' => '/en/groups/{slug}/{id}/invitation',
        'fr' => '/fr/groupes/{slug}/{id}/invitation',
    ],
        name: 'show_logged',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6]
    )]
    public function showLogged(string $id, #[CurrentUser] ?User $user): Response
    {
        $group = $this->getGroup($id);
        $showQuitGroupChoices = $this->getShowQuitGroupsChoices($group, $user);

        return $this->render('pages/group/show.html.twig', compact('group', 'showQuitGroupChoices'));
    }

    private function getShowQuitGroupsChoices(Group $group, ?User $user): bool
    {
        return $this->productManager->hasProductsOnlyInGroup($group, $user);
    }

    #[Route([
        'en' => '/{_locale}/groups/{slug}/{id}/members',
        'fr' => '/{_locale}/groupes/{slug}/{id}/membres',
    ],
        name: 'members',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6]
    )]
    public function showMembers(Request $request, string $id): Response
    {
        $group = $this->getGroup($id);
        $page = $this->getPage($request);
        $form = $this->createForm(GroupSelectFormType::class)->handleRequest($request);

        $memberName = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $memberName */
            $memberName = $form->get('q')->getData();
        }

        /** @var Query $query */
        $query = $this->queryBus->query(new GetGroupMembersQuery(Uuid::fromString($id), $memberName));
        $pagination = $this->paginator->paginate($query, $page, self::MAX_ELEMENT_BY_PAGE);

        return $this->render('pages/group/members.html.twig', compact('pagination', 'form', 'group'));
    }
}
