<?php

declare(strict_types=1);

namespace App\Twig;

/**
 * Gather all extensions implementing the FlysystemImagesInterface.
 */
final readonly class ImagesExtensionCollection
{
    /**
     * @param iterable<FlysystemImagesInterface> $extensions
     */
    public function __construct(
        private iterable $extensions,
    ) {
    }

    /**
     * @return iterable<FlysystemImagesInterface>
     */
    public function getExtensions(): iterable
    {
        return $this->extensions;
    }
}
