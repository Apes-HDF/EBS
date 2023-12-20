<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\Product\CreateProductAvailabilityType;
use App\Message\Command\User\Product\CreateProductUnavailabilityCommand;
use App\Message\Query\Product\GetProductByIdQuery;
use App\Message\Query\Product\GetProductUnavailabilitiesQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use App\Repository\ProductRepository;
use App\Security\Voter\ProductVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

final class ProductAvailabilityController extends AbstractController
{
    use FlashTrait;
    use i18nTrait;
    use SecurityTrait;

    public const ROUTE = 'app_user_product_availabilities';

    public function __construct(
      public readonly ProductRepository $productRepository,
      private readonly QueryBus $queryBus,
      private readonly CommandBus $commandBus,
    ) {
    }

    #[isGranted(User::ROLE_USER)]
    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/my-products/{id}/availabilities',
        'fr' => MyAccountAction::BASE_URL_FR.'/mes-produits/{id}/disponibilites',
    ],
        name: self::ROUTE,
        requirements: ['id' => Requirement::UUID_V6],
    )]
    public function __invoke(Request $request, string $id): Response
    {
        /** @var Product $product */
        $product = $this->queryBus->query(new GetProductByIdQuery(Uuid::fromString($id), ProductVoter::EDIT));

        $unavailabilities = $this->queryBus->query(new GetProductUnavailabilitiesQuery($product->getId()));

        $form = $this->createForm(CreateProductAvailabilityType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \DateTimeImmutable $startAt */
            $startAt = $form->get('startAt')->getData();
            /** @var \DateTimeImmutable $endAt */
            $endAt = $form->get('endAt')->getData();

            $command = new CreateProductUnavailabilityCommand($product->getId(), $startAt, $endAt);
            $this->commandBus->dispatch($command);

            $this->addFlashSuccess($this->getI18nPrefix().'.success');

            return $this->redirectToRoute('app_product_show', ['id' => $id, 'slug' => $product->getSlug()]);
        }

        return $this->render('pages/product/product_availability.html.twig', compact('product', 'id', 'form', 'unavailabilities'));
    }
}
