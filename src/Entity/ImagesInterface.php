<?php

declare(strict_types=1);

namespace App\Entity;

interface ImagesInterface
{
    /**
     * @return array<string>|null
     */
    public function getImages(): ?array;
}
