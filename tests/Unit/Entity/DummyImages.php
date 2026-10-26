<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ImagesInterface;

final class DummyImages implements ImagesInterface
{
    /**
     * @return array<string>
     */
    public function getImages(): array
    {
        return ['foo.png'];
    }
}
