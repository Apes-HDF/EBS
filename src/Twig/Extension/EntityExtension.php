<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\ImageInterface;
use App\Entity\ImagesInterface;
use Twig\Attribute\AsTwigFunction;

class EntityExtension
{
    #[AsTwigFunction(name: 'is_image_entity')]
    public function isImageEntity(object $entity): bool
    {
        return $entity instanceof ImageInterface;
    }

    #[AsTwigFunction(name: 'is_images_entity')]
    public function isImagesEntity(object $entity): bool
    {
        return $entity instanceof ImagesInterface;
    }
}
