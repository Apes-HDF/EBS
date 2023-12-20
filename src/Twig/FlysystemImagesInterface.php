<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImagesInterface;

interface FlysystemImagesInterface
{
    public function supports(ImagesInterface $entity): bool;

    public function getPublicUrl(string $image): ?string;
}
