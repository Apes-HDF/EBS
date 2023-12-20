<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Controller\PaginationTrait;
use App\Controller\RequestTrait;
use App\Dto\Product\Search;
use App\Entity\User;
use App\Message\Query\User\Account\GetUserQuery;
use App\MessageBus\QueryBus;
use App\Search\Meilisearch;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

/**
 * @see ProfileActionTest
 */
final class ProfileAction extends AbstractController
{
    use PaginationTrait;
    use RequestTrait;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly PaginatorInterface $paginator,
        private readonly Meilisearch $meilisearch,
    ) {
    }

    #[Route([
        'en' => '/{_locale}/user/{userId}',
        'fr' => '/{_locale}/utilisateur/{userId}',
    ],
        name: 'app_user_profile',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6, 'userId' => Requirement::UUID_V6]
    )]
    public function __invoke(Request $request, string $userId, #[CurrentUser] ?User $currentUser): Response
    {
        try {
            /** @var User $user */
            $user = $this->queryBus->query(new GetUserQuery(Uuid::fromString($userId)));
        } catch (HandlerFailedException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $searchDto = new Search('', $this->getPage($request), $currentUser);
        $searchDto->place = $user;

        return $this->render('pages/user/account/profile.html.twig', [
            'user' => $user,
            'objects_pagination' => $this->paginate($this->meilisearch->searchObjects($searchDto)),
            'services_pagination' => $this->paginate($this->meilisearch->searchServices($searchDto)),
        ]);
    }
}
