<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * This trait contains the specific fields for the services.
 *
 * @see Product
 */
trait ProductServiceTrait
{
    /**
     * Duration of the service (free text).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $duration = null;

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
}
