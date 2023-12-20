<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImageInterface;
use App\Entity\Menu;
use League\Flysystem\FilesystemOperator;
use Twig\Extension\AbstractExtension;

class MenuExtension extends AbstractExtension implements FlysystemImageInterface
{
    public function __construct(
        public readonly FilesystemOperator $defaultStorage,
    ) {
    }

    public function supports(ImageInterface $entity): bool
    {
        return $entity instanceof Menu;
    }

    /**
     * Use the Flysytem helper. Locally it uses the public_url parameter.
     */
    public function getPublicUrl(ImageInterface $menu): ?string
    {
        /** @var Menu $menu */

        return $this->defaultStorage->publicUrl((string) $menu->getImage());
    }
}
