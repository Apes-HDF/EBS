<?php

declare(strict_types=1);

namespace App\Search\Document;

use App\Entity\Address;

/**
 * DTO to store lat/long data of the product.
 *
 * @see Address
 */
final class GeoDocument
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }

    public static function fromAddress(Address $address): self
    {
        return new self(
            lat: (float) $address->getLatitude(),
            lng: (float) $address->getLongitude(),
        );
    }
}
