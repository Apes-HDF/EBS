<?php

declare(strict_types=1);

namespace App\Message\Query\Admin\User;

use App\Entity\Address;
use Webmozart\Assert\Assert;

/**
 * Query to get the geolocation of an address thanks to a given provider.
 *
 * @see UserAddressQueryHandler
 */
final class UserAddressQuery
{
    public function __construct(Address $address)
    {
        Assert::stringNotEmpty($address->getAddress());
        $this->address = $address->getAddress();
        $this->addressSupplement = $address->getAddressSupplement();
        Assert::stringNotEmpty($address->getLocality());
        $this->locality = $address->getLocality();
        Assert::stringNotEmpty($address->getPostalCode());
        $this->postalCode = $address->getPostalCode();
        Assert::stringNotEmpty($address->getCountry());
        $this->country = $address->getCountry();
    }

    public string $address;
    public ?string $addressSupplement = null;
    public string $locality;
    public string $postalCode;
    public string $country;

    /**
     * Format a full address with the user input. We don't use the supplument or
     * nothing is found by the provider when using it.
     */
    public function getAddressForQuery(): string
    {
        return $this->address.', '.$this->postalCode.', '.$this->locality;
    }
}
