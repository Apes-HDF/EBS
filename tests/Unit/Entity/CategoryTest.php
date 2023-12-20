<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CategoryTest extends TestCase
{
    public function testCategory(): void
    {
        $category = new Category();
        $id = Uuid::v6();
        self::assertSame($id, $category->setId($id)->getId());
        self::assertSame('cat-2', $category->setSlug('cat-2')->getSlug());
        self::assertFalse($category->hasParent());
    }
}
