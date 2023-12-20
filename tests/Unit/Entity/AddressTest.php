<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Address;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AddressTest extends TestCase
{
    public function testAddress(): void
    {
        $address = new Address();
        $id = Uuid::v6();
        self::assertSame($id, $address->setId($id)->getId());
        self::assertSame('full addr', $address->setDisplayName('full addr')->getDisplayName());
        self::assertSame('full addr', (string) $address);
        self::assertSame('1 bis', $address->setStreetNumber('1 bis')->getStreetNumber());
        self::assertSame('Winston Churchill', $address->setStreetName('Winston Churchill')->getStreetName());
        self::assertSame('Lille', $address->setLocality('Lille')->getLocality());
        self::assertSame('Fives', $address->setSubLocality('Fives')->getSubLocality());
        self::assertSame('Lille (Fives)', $address->getSubAndLocality());
        self::assertSame('50.63', $address->setLatitude('50.63')->getLatitude());
        self::assertSame('3.01', $address->setLongitude('3.01')->getLongitude());
        self::assertSame('nominatim', $address->setProvidedBy('nominatim')->getProvidedBy());
        self::assertSame('copyright', $address->setAttribution('copyright')->getAttribution());
        self::assertSame('way', $address->setOsmType('way')->getOsmType());
        self::assertSame(12345, $address->setOsmId(12345)->getOsmId());
    }
}
