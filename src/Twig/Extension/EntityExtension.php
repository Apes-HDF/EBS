<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\ImageInterface;
use App\Entity\ImagesInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EntityExtension extends AbstractExtension
{
    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_image_entity', $this->isImageEntity(...)),
            new TwigFunction('is_images_entity', $this->isImagesEntity(...)),
        ];
    }

    public function isImageEntity(object $entity): bool
    {
        return $entity instanceof ImageInterface;
    }

    public function isImagesEntity(object $entity): bool
    {
        return $entity instanceof ImagesInterface;
    }
}
