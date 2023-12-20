<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Controller\i18nTrait;
use App\Controller\PaginationTrait;
use App\Controller\RequestTrait;
use App\Dto\Product\Search;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\Product\SearchFormType;
use App\Message\Query\Product\GetProductByIdQuery;
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

use function Symfony\Component\String\u;

/**
 * @see ProductControllerTest
 */
#[Route(name: 'app_product_')]
final class ProductController extends AbstractController
{
    use RequestTrait;
    use i18nTrait;
    use PaginationTrait;

    public const MAX_ELEMENT_BY_PAGE = 20;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly PaginatorInterface $paginator,
        private readonly Meilisearch $meilisearch,
    ) {
    }

    #[Route([
        'en' => '/{_locale}/product',
        'fr' => '/{_locale}/produits',
    ],
        name: 'list'
    )]
    public function list(Request $request, #[CurrentUser] ?User $user): Response
    {
        $page = $this->getPage($request);
        $q = u($request->query->get('q'))->toString();
        $searchDto = new Search($q, $page, $user);

        // The DTO is modified with selected values if the form is submitted and valid
        $searchForm = $this->createForm(SearchFormType::class, $searchDto)->handleRequest($request);

        return $this->render('pages/product/list.html.twig', [
            'objects_pagination' => $this->paginate($this->meilisearch->searchObjects($searchDto)),
            'services_pagination' => $this->paginate($this->meilisearch->searchServices($searchDto)),
            'search_form' => $searchForm,
        ]);
    }

    /**
     * The slug is only for SEO.
     */
    #[Route([
        'en' => '/{_locale}/product/{slug}/{id}',
        'fr' => '/{_locale}/produits/{slug}/{id}',
    ],
        name: 'show',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6]
    )]
    public function show(string $slug, string $id): Response
    {
        try {
            /** @var Product $product */
            $product = $this->queryBus->query(new GetProductByIdQuery(Uuid::fromString($id)));
        } catch (HandlerFailedException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $this->render('pages/product/show.html.twig', compact('slug', 'id', 'product'));
    }
}
