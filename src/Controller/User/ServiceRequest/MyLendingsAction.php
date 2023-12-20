<?php

declare(strict_types=1);

namespace App\Controller\User\ServiceRequest;

use App\Controller\RequestTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\ServiceRequest\UserLendingProductSelectFormType;
use App\Message\Query\User\ServiceRequest\GetLendingsQuery;
use App\MessageBus\QueryBus;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see MyLendingsActionTest
 */
final class MyLendingsAction extends AbstractController
{
    use SecurityTrait;
    use RequestTrait;

    private const MAX_ELEMENT_BY_PAGE = 10;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[isGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-lendings',
        'fr' => MyAccountAction::BASE_URL_FR.'/mes-prets',
    ], name: 'app_user_my_lendings')]
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(UserLendingProductSelectFormType::class)->handleRequest($request);

        /** @var ?ArrayCollection<int, Product> $selectedProducts */
        $selectedProducts = $form->get('product')->getData();

        $query = $this->queryBus->query(new GetLendingsQuery($user->getId(), $selectedProducts));
        $pagination = $this->paginator->paginate($query, $this->getPage($request), self::MAX_ELEMENT_BY_PAGE);

        return $this->render('pages/account/lendings/list.html.twig', compact('pagination', 'form', 'selectedProducts'));
    }
}
