<?php

declare(strict_types=1);

namespace App\Message\Query\Product;

use App\Controller\Product\ProductController;
use Symfony\Component\Uid\Uuid;

/**
 * @see ProductController::show()
 * @see ObjectController::edit()
 * @see GetProductByIdQueryHandler
 */
final class GetProductByIdQuery
{
    public function __construct(
        // the product uuid
        public readonly Uuid $id,

        // optionnal attribute to check on the object
        public readonly ?string $attribute = null,
    ) {
    }
}
