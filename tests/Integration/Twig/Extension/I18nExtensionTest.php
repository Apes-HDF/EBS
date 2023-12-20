<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Test\ContainerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class I18nExtensionTest extends KernelTestCase
{
    use ContainerTrait;

    public function testI18nExtension(): void
    {
        self::bootKernel();
        $extension = $this->getI18nExtension();
        // we use use trimSuffix() not trimEnd() in getI18Prefix(), otherwise the final "t" would be removed
        self::assertSame('templates.pages.group.list', $extension->getI18Prefix('pages/group/list.html.twig'));
    }
}
