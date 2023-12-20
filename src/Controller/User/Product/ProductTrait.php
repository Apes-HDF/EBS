<?php

declare(strict_types=1);

namespace App\Controller\User\Product;

use App\Entity\Product;
use App\Message\Query\Product\GetProductByIdQuery;
use App\Security\Voter\ProductVoter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Uid\Uuid;

trait ProductTrait
{
    private function getProductForEdit(string $id): Product
    {
        try {
            /** @var Product $product */
            $product = $this->queryBus->query(new GetProductByIdQuery(Uuid::fromString($id), ProductVoter::EDIT));
        } catch (HandlerFailedException $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $this->createNotFoundException($e->getMessage());
        }

        return $product;
    }
}
