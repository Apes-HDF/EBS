<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use App\Enum\Product\ProductType;

final class ObjectCategorySelectFormType extends AbstractProductCategorySelectFormType
{
    public function getProductType(): ProductType
    {
        return ProductType::OBJECT;
    }
}
