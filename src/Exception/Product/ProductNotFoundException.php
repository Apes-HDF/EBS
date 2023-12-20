<?php

declare(strict_types=1);

namespace App\Exception\Product;

use Symfony\Component\Uid\Uuid;

/**
 * Thrown if a product with a given is not found in the database (it was deleted
 * for example).
 */
final class ProductNotFoundException extends \DomainException
{
    public function __construct(Uuid $id)
    {
        parent::__construct("Product with id $id not found.");
    }
}
