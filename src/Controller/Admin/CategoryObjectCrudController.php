<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Enum\Product\ProductType;

/**
 * Specific page for objects' categories.
 *
 * @see CategoryObjectCrudControllerTest
 */
final class CategoryObjectCrudController extends AbstractCategoryCrudController
{
    public function getCategoryType(): ProductType
    {
        return ProductType::OBJECT;
    }

    public function getEntityLabelInPlural(): string
    {
        return 'categories.objects';
    }
}
