<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\User;

use App\Entity\Address;
use App\Geocoder\GeoProvider;
use App\Message\Query\Admin\User\UserAddressQuery;
use App\MessageHandler\Query\User\UserAddressQueryHandler;
use Geocoder\Exception\InvalidServerResponse;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserAddressQueryHandlerTest extends KernelTestCase
{
    public function testInvokeException(): void
    {
        self::bootKernel();
        $nominatimGeocoder = $this->getMockBuilder(GeoProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nominatimGeocoder->method('getAddressCollection')
            ->willThrowException(new InvalidServerResponse());

        $handler = new UserAddressQueryHandler($nominatimGeocoder);
        $this->expectException(\RuntimeException::class);

        $address = (new Address())
            ->setAddress('foo')
            ->setLocality('foo')
            ->setPostalCode('foo')
            ->setCountry('FR')
        ;

        $handler(new UserAddressQuery($address));
    }
}
