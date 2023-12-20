<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImageInterface;

interface FlysystemImageInterface
{
    public function supports(ImageInterface $entity): bool;

    public function getPublicUrl(ImageInterface $entity): ?string;
}
