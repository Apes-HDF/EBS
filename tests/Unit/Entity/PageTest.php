<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Page;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class PageTest extends TestCase
{
    public function testPage(): void
    {
        $category = new Page();
        $id = Uuid::v6();
        self::assertSame($id, $category->setId($id)->getId());
        self::assertSame('page', $category->setSlug('page')->getSlug());
    }
}
