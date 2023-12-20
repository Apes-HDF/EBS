<?php

declare(strict_types=1);

namespace App\Tests\Integration\Translator;

use App\Translator\NoTranslator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class NotranslatorTest extends KernelTestCase
{
    public function testNoTranslator(): void
    {
        self::bootKernel();
        /** @var NoTranslator $translator */
        $translator = self::getContainer()->get(NoTranslator::class);
        self::assertSame('fr', $translator->getLocale());
        $translator->setLocale('en');
        self::assertSame('en', $translator->getLocale());
        self::assertSame([], $translator->getCatalogues());
    }
}
