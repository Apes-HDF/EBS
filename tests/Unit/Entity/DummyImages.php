<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ImagesInterface;

final class DummyImages implements ImagesInterface
{
    public function getImages(): ?array
    {
        return ['foo.png'];
    }
}
