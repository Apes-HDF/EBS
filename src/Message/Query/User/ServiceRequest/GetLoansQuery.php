<?php

declare(strict_types=1);

namespace App\Message\Query\User\ServiceRequest;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Uuid;

/**
 * @see GetLoansQueryHandler
 */
final readonly class GetLoansQuery
{
    public function __construct(
        // the user uuid
        public Uuid $userId,

        // array of selected products
        /** @var array<Product>|ArrayCollection<int, Product>|null $products */
        public mixed $products,
    ) {
    }
}
