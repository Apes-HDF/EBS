<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Test\ContainerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TwigExtensionTest extends KernelTestCase
{
    use ContainerTrait;

    public function testI18nExtension(): void
    {
        self::bootKernel();
        $extension = $this->getTwigExtension();
        self::assertSame('my_controler', $extension->snake('myControler'));
    }
}
