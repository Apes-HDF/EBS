<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\Product\ProductType;

/**
 * Specific page for objects' categories.
 */
final class CategoryServiceCrudController extends AbstractCategoryCrudController
{
    public function getCategoryType(): ProductType
    {
        return ProductType::SERVICE;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'categories.services';
    }
}
