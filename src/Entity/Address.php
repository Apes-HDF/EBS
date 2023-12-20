<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use App\Repository\AddressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Geocoder\Model\Address as GeocoderAddress;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Table]
/**
 * We take the same naming as the Geocoder bundle.
 *
 * @see GeocoderAddress
 * @see NominatimAddress
 */
class Address implements \Stringable
{
    use TimestampableEntity;

    private const LATLONG_PRECISION = 11;
    private const LATLONG_SCALE = 7;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    /**
     * This is the address as entered by the user (streetNumber + locality).
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $address;

    /**
     * Additional information for the address, eg: APT 555.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $addressSupplement = null;

    /**
     * Reformated address by the Geocoder provider.
     *
     * @example 82, Rue Winston Churchill, Lomme, Lille, Nord, Hauts-de-France, France métropolitaine, 59160, France
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $displayName = null;

    /**
     * Streetnumber, is a string because it can have a suffix (bis, ter...), eg: "2 bis".
     */
    #[ORM\Column(type: Types::STRING, length: 20, nullable: false)]
    #[Assert\Length(max: 255)]
    private string $streetNumber;

    /**
     * Name of the street, eg: "Rue Winston Churchill".
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\Length(max: 255)]
    private string $streetName;

    /**
     * Sublocality (city), eg: "Fives" (locality is Lille in this case).
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $subLocality = null;

    /**
     * Name of the locality (city), eg: "Lille".
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $locality = '';

    /**
     * Postal code, eg: "59160".
     */
    #[ORM\Column(type: Types::STRING, length: 10, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $postalCode;

    /**
     * ISO code of the country, eg: "FR".
     *
     * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
     */
    #[ORM\Column(type: Types::STRING, length: 2, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Country]
    private string $country;

    /**
     * Latitude of the address (north/south), eg: "50.6322562".
     *
     * @see https://en.wikipedia.org/wiki/Latitude
     */
    #[ORM\Column(type: Types::DECIMAL, precision: self::LATLONG_PRECISION, scale: self::LATLONG_SCALE, nullable: true)]
    private string $latitude;

    /**
     * Longitude of the address (eat/ouest), eg: "3.0173079".
     *
     * @see https://en.wikipedia.org/wiki/Longitude
     */
    #[ORM\Column(type: Types::DECIMAL, precision: self::LATLONG_PRECISION, scale: self::LATLONG_SCALE, nullable: true)]
    private string $longitude;

    /**
     * Name of the provider, eg: "nominatim".
     */
    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private string $providedBy;

    /**
     * Copyright for the address, eg: "'Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright".
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private string $attribution;

    /**
     * OpenStreetMap identifier type (way, building...).
     */
    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $osmType = null;

    /**
     * OpenStreetMap identifier, usefull to create link to maps.
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private int $osmId;

    public function __toString(): string
    {
        return (string) $this->displayName;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressSupplement(): ?string
    {
        return $this->addressSupplement;
    }

    public function setAddressSupplement(?string $addressSupplement): Address
    {
        $this->addressSupplement = $addressSupplement;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getStreetNumber(): string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getStreetName(): string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function hasLocality(): bool
    {
        return $this->locality !== '';
    }

    public function setLocality(string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    public function getSubLocality(): ?string
    {
        return $this->subLocality;
    }

    public function setSubLocality(?string $subLocality): self
    {
        $this->subLocality = $subLocality;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getProvidedBy(): string
    {
        return $this->providedBy;
    }

    public function setProvidedBy(string $providedBy): Address
    {
        $this->providedBy = $providedBy;

        return $this;
    }

    public function getAttribution(): string
    {
        return $this->attribution;
    }

    public function setAttribution(string $attribution): Address
    {
        $this->attribution = $attribution;

        return $this;
    }

    public function getOsmType(): ?string
    {
        return $this->osmType;
    }

    public function setOsmType(?string $osmType): Address
    {
        $this->osmType = $osmType;

        return $this;
    }

    public function getOsmId(): int
    {
        return $this->osmId;
    }

    public function setOsmId(int $osmId): Address
    {
        $this->osmId = $osmId;

        return $this;
    }

    // End of basic setters/getters ————————————————————————————————————————————

    /**
     * Format a full address with the user input (for address update step2).
     */
    public function getFullAddress(): string
    {
        $addressSupplement = u($this->addressSupplement)->isEmpty() ? '' : ', '.$this->addressSupplement;

        return $this->address.$addressSupplement.', '.$this->postalCode.', '.$this->locality.', '.$this->country;
    }

    /**
     * Override the properties of the old address.
     */
    public function setFromAddressUpdateStep1(Address $newAddress): self
    {
        $this->setAddress($newAddress->getAddress());
        $this->setAddressSupplement($newAddress->getAddressSupplement());
        $this->setPostalCode($newAddress->getPostalCode());
        $this->setLocality($newAddress->getLocality());
        $this->setCountry($newAddress->getCountry());

        return $this;
    }

    public function getSubAndLocality(): string
    {
        if (u($this->subLocality)->isEmpty()) {
            return $this->locality;
        }

        return $this->locality.' ('.$this->subLocality.')';
    }
}
