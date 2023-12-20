<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Symfony\Component\String\u;

class TwigExtension extends AbstractExtension
{
    public function __construct(
        public readonly ImageExtensionCollection $imageExtensionCollection,
        public readonly ImagesExtensionCollection $imagesExtensionCollection,
    ) {
    }

    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('snake', $this->snake(...)),
        ];
    }

    public function snake(?string $sring): string
    {
        return u($sring)->snake()->toString();
    }
}
