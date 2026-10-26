<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImageInterface;
use App\Entity\ImagesInterface;
use Twig\Attribute\AsTwigFilter;

class FlysystemExtension
{
    public function __construct(
        public readonly ImageExtensionCollection $imageExtensionCollection,
        public readonly ImagesExtensionCollection $imagesExtensionCollection,
    ) {
    }

    /**
     * Loop through all extensions implementing the Flysystem publicUrl() function.
     */
    #[AsTwigFilter(name: 'public_url')]
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
    #[AsTwigFilter(name: 'public_url_image')]
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
