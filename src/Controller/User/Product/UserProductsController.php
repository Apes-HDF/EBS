<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Controller\RequestTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\Product\ObjectCategorySelectFormType;
use App\Form\Type\Product\ServiceCategorySelectFormType;
use App\Message\Query\User\GetUserObjectsQuery;
use App\Message\Query\User\GetUserServicesQuery;
use App\MessageBus\QueryBus;
use App\Repository\CategoryRepository;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see UserProductsControllerTest
 */
#[isGranted(User::ROLE_USER)]
#[Route(name: 'app_user_')]
final class UserProductsController extends AbstractController
{
    use RequestTrait;

    public const MAX_ELEMENT_BY_PAGE = 5;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly PaginatorInterface $paginator,
        public readonly CategoryRepository $categoryRepository,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    /**
     * @implements PaginationInterface<int,Product>
     *
     * @return PaginationInterface<int,Product>
     */
    private function paginate(Query $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate($query, $page, self::MAX_ELEMENT_BY_PAGE);
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-objects',
        'fr' => MyAccountAction::BASE_URL_FR.'/mes-objets',
    ], name: 'objects')]
    public function userObjects(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ObjectCategorySelectFormType::class)->handleRequest($request);
        $category = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Category $category */
            $category = $form->get('category')->getData();
        }

        /** @var Query $query */
        $query = $this->queryBus->query(new GetUserObjectsQuery($user->getId(), $category?->getId()));
        $pagination = $this->paginate($query, $this->getPage($request));

        return $this->render('pages/account/product/list.html.twig', compact('pagination', 'form'));
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-services',
        'fr' => MyAccountAction::BASE_URL_FR.'/mes-services',
    ], name: 'services')]
    public function userServices(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ServiceCategorySelectFormType::class)->handleRequest($request);
        $category = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ?Category $category */
            $category = $form->get('category')->getData();
        }
        /** @var Query $query */
        $query = $this->queryBus->query(new GetUserServicesQuery($user->getId(), $category?->getId()));
        $pagination = $this->paginate($query, $this->getPage($request));

        if ($this->configurationRepository->getServicesParameter()) {
            return $this->render('pages/account/product/list.html.twig', compact('pagination', 'form'));
        } else {
            throw new GoneHttpException('there is no services');
        }
    }
}
