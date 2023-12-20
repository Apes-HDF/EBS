<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Product;
use App\Entity\User;
use App\Message\Command\User\Product\DuplicateProductCommand;
use App\Message\Query\Product\GetProductByIdQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use App\Security\Voter\ProductVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * @see DuplicateProductActionTest
 */
#[IsGranted(User::ROLE_USER)]
final class DuplicateProductAction extends AbstractController
{
    use SecurityTrait;
    use FlashTrait;
    use i18nTrait;

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/objects/{id}/duplicate',
        'fr' => MyAccountAction::BASE_URL_FR.'/objets/{id}/dupliquer',
    ],
        name: 'app_user_product_duplicate',
        requirements: ['slug' => Requirement::ASCII_SLUG, 'id' => Requirement::UUID_V6]
    )]
    public function __invoke(string $id): Response
    {
        try {
            /** @var Product $product */
            $product = $this->queryBus->query(new GetProductByIdQuery(Uuid::fromString($id), ProductVoter::DUPLICATE));
        } catch (HandlerFailedException $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $this->createNotFoundException($e->getMessage());
        }

        $command = new DuplicateProductCommand($product->getId(), ProductVoter::DUPLICATE);
        /** @var Product $duplicatedProduct */
        $duplicatedProduct = $this->commandBus->dispatch($command);
        $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

        return $this->redirectToRoute('app_'.$duplicatedProduct->getType()->value.'_edit', ['id' => $duplicatedProduct->getId()]);
    }
}
