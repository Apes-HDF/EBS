<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImagesInterface;
use App\Entity\Product;
use League\Flysystem\FilesystemOperator;
use Twig\Extension\AbstractExtension;

class ProductExtension extends AbstractExtension implements FlysystemImagesInterface
{
    public function __construct(
        public readonly FilesystemOperator $productStorage,
    ) {
    }

    public function supports(ImagesInterface $entity): bool
    {
        return $entity instanceof Product;
    }

    public function getPublicUrl(string $image): ?string
    {
        return $this->productStorage->publicUrl($image);
    }
}
