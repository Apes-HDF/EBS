<?php

declare(strict_types=1);

namespace App\Geocoder;

use App\Entity\Address;
use Geocoder\Model\AddressCollection;

interface GeoProviderInterface
{
    public const DEFAULT_COUNTRY = 'FR';

    public function getAddressCollection(string $text, int $limit, string $country = self::DEFAULT_COUNTRY): AddressCollection;

    public function getAddress(string $text): ?Address;
}
