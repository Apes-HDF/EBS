<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\ImageInterface;
use App\Entity\User;
use League\Flysystem\FilesystemOperator;
use Twig\Extension\AbstractExtension;

class UserExtension extends AbstractExtension implements FlysystemImageInterface
{
    public function __construct(
        public readonly FilesystemOperator $userStorage,
    ) {
    }

    public function supports(ImageInterface $entity): bool
    {
        return $entity instanceof User;
    }

    /**
     * Use the Flysytem helper. Locally it uses the public_url parameter.
     */
    public function getPublicUrl(ImageInterface $user): ?string
    {
        /** @var User $user */

        return $this->userStorage->publicUrl((string) $user->getImage());
    }
}
