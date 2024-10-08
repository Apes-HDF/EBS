<?php

declare(strict_types=1);

namespace App\Controller\User\ServiceRequest;

use App\Controller\RequestTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\ServiceRequest\UserLoansProductSelectFormType;
use App\Message\Query\User\ServiceRequest\GetLoansQuery;
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
 * @see MyLoansActionTest
 */
class MyLoansAction extends AbstractController
{
    use RequestTrait;

    private const MAX_ELEMENT_BY_PAGE = 10;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[IsGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-loans',
        'fr' => MyAccountAction::BASE_URL_FR.'/mes-emprunts',
    ], name: 'app_user_my_loans')]
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(UserLoansProductSelectFormType::class)->handleRequest($request);

        /** @var ?ArrayCollection<int, Product> $selectedProducts */
        $selectedProducts = $form->get('product')->getData();

        $query = $this->queryBus->query(new GetLoansQuery($user->getId(), $selectedProducts));
        $pagination = $this->paginator->paginate($query, $this->getPage($request), self::MAX_ELEMENT_BY_PAGE);

        return $this->render('pages/account/loans/list.html.twig', compact('pagination', 'form'));
    }
}
