<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Doctrine\Manager\ProductManager;
use App\Entity\Product;
use App\Entity\User;
use App\Message\Query\Product\GetProductByIdQuery;
use App\MessageBus\QueryBus;
use App\Security\Voter\ProductVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * @see DeleteProductPhotoActionTest
 */
#[IsGranted(User::ROLE_USER)]
final class DeleteProductPhotoAction extends AbstractController
{
    use SecurityTrait;
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly ProductManager $productManager,
    ) {
    }

    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/objects/{productId}/delete-photo/{image}',
        name: 'app_user_product_delete_photo',
        requirements: [
            'productId' => Requirement::UUID_V6,
        ]
    )]
    public function __invoke(string $productId, string $image): Response
    {
        try {
            /** @var Product $product */
            $product = $this->queryBus->query(new GetProductByIdQuery(Uuid::fromString($productId), ProductVoter::EDIT));
        } catch (HandlerFailedException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }
        $this->productManager->deleteImage($product, $image);
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

        return $this->redirectToRoute('app_'.$product->getType()->value.'_edit', ['id' => $product->getId()]);
    }
}
