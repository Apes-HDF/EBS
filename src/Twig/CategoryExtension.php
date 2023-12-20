<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Category;
use App\Entity\ImageInterface;
use League\Flysystem\FilesystemOperator;
use Twig\Extension\AbstractExtension;

class CategoryExtension extends AbstractExtension implements FlysystemImageInterface
{
    public function __construct(
        public readonly FilesystemOperator $categoryStorage,
    ) {
    }

    public function supports(ImageInterface $entity): bool
    {
        return $entity instanceof Category;
    }

    /**
     * Use the Flysytem helper. Locally it uses the public_url parameter.
     */
    public function getPublicUrl(ImageInterface $category): ?string
    {
        /** @var Category $category */

        return $this->categoryStorage->publicUrl((string) $category->getImage());
    }
}
