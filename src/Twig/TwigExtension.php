<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Attribute\AsTwigFilter;

use function Symfony\Component\String\u;

class TwigExtension
{
    public function __construct(
        public readonly ImageExtensionCollection $imageExtensionCollection,
        public readonly ImagesExtensionCollection $imagesExtensionCollection,
    ) {
    }

    #[AsTwigFilter(name: 'snake')]
    public function snake(?string $sring): string
    {
        return u($sring)->snake()->toString();
    }
}
