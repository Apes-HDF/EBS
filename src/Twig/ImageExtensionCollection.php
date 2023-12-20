<?php

declare(strict_types=1);

namespace App\Twig;

/**
 * Gather all extensions implementing the FlysystemImageInterface.
 */
final class ImageExtensionCollection
{
    /**
     * @param iterable<FlysystemImageInterface> $extensions
     */
    public function __construct(
        private readonly iterable $extensions,
    ) {
    }

    /**
     * @return iterable<FlysystemImageInterface>
     */
    public function getExtensions(): iterable
    {
        return $this->extensions;
    }
}
