<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImageInterface;
use App\Entity\ImagesInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FlysystemExtension extends AbstractExtension
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
            new TwigFilter('public_url', $this->getPublicUrl(...)),
            new TwigFilter('public_url_image', $this->getPublicUrlImage(...)),
        ];
    }

    /**
     * Loop through all extensions implementing the Flysystem publicUrl() function.
     */
    public function getPublicUrl(ImageInterface $entity): ?string
    {
        foreach ($this->imageExtensionCollection->getExtensions() as $extension) {
            if ($extension->supports($entity)) {
                return $extension->getPublicUrl($entity);
            }
        }

        throw new \LogicException('This entity is not managed by this function, add the case.');
    }

    /**
     * Same as getPublicUrl() but for entities having multiple images associated.
     */
    public function getPublicUrlImage(ImagesInterface $entity, string $image): ?string
    {
        foreach ($this->imagesExtensionCollection->getExtensions() as $extension) {
            if ($extension->supports($entity)) {
                return $extension->getPublicUrl($image);
            }
        }

        throw new \LogicException('This entity is not managed by this function, add the case.');
    }
}
