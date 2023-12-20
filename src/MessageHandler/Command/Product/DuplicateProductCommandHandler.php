<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Product;

use App\Doctrine\Manager\ProductManager;
use App\Entity\Product;
use App\Message\Command\User\Product\DuplicateProductCommand;
use App\Repository\ProductRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @see DuplicateProductCommandHandlerTest
 */
#[AsMessageHandler]
class DuplicateProductCommandHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductManager $productManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function __invoke(DuplicateProductCommand $message): Product
    {
        $product = $this->productRepository->get($message->productId);
        if ($message->attribute !== null && !$this->authorizationChecker->isGranted($message->attribute, $product)) {
            throw new AccessDeniedException();
        }
        $duplicated = $this->productManager->duplicate($product);
        $this->productManager->save($duplicated, true);

        return $duplicated;
    }
}
