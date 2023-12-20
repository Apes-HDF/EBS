<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\User;
use App\MessageBus\QueryBus;
use App\Repository\ProductAvailabilityRepository;
use App\Repository\ProductRepository;
use App\Security\Voter\ProductVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
final class DeleteProductUnavailabilityAction extends AbstractController
{
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        public readonly QueryBus $queryBus,
        public readonly ProductRepository $productRepository,
        public readonly ProductAvailabilityRepository $productAvailabilityRepository,
        private readonly Security $security,
    ) {
    }

    #[Route(path: [
            'en' => MyAccountAction::BASE_URL_EN.'/products/unavailability/{id}/delete',
            'fr' => MyAccountAction::BASE_URL_FR.'/produits/indisponibilite/{id}/supprimer',
        ],
        name: 'app_user_product_delete_availability',
        requirements: [
            'id' => Requirement::UUID_V6,
        ]
    )]
    public function __invoke(string $id): Response
    {
        $productUnavailability = $this->productAvailabilityRepository->get($id);

        if (!$this->security->isGranted(ProductVoter::EDIT, $productUnavailability->getProduct())) {
            throw new AccessDeniedHttpException('Unauthorized to delete this product unavailibility');
        }

        $this->productAvailabilityRepository->deleteProductUnavailability($productUnavailability);
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

        return $this->redirectToRoute(ProductAvailabilityController::ROUTE, ['id' => $productUnavailability->getProduct()->getId()]);
    }
}
