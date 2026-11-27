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
    /**
     * Decimal places kept for an owner whose address is not public (any user
     * except a place): 0.01° is about 1.1 km of latitude and 0.7 to 0.8 km of
     * longitude in metropolitan France, the search results stay at this scale.
     */
    final public const PRIVATE_PRECISION = 2;

    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }

    /**
     * @param int|null $precision number of decimal places to keep, null to keep the exact coordinates
     */
    public static function fromAddress(Address $address, ?int $precision = null): self
    {
        $lat = (float) $address->getLatitude();
        $lng = (float) $address->getLongitude();
        if ($precision !== null) {
            $lat = round($lat, $precision);
            $lng = round($lng, $precision);
        }

        return new self(
            lat: $lat,
            lng: $lng,
        );
    }
}
