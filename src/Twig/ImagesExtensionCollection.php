<?php

declare(strict_types=1);

namespace App\Twig;

/**
 * Gather all extensions implementing the FlysystemImagesInterface.
 */
final class ImagesExtensionCollection
{
    /**
     * @param iterable<FlysystemImagesInterface> $extensions
     */
    public function __construct(
        private readonly iterable $extensions,
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
