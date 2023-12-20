<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ImageInterface;

final class DummyImage implements ImageInterface
{
    public function getImage(): ?string
    {
        return 'dummy';
    }
}
