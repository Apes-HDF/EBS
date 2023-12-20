<?php

declare(strict_types=1);

namespace App\Message\Query\Admin\Poc;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query to the geolocation of an address thanks to a given provider.
 *
 * @see GeolocQueryHandler
 */
final class PocGeolocQuery
{
    #[Assert\NotBlank()]
    public string $address = '82 Rue Winston Churchill, 59160, Lomme, FRANCE';
}
