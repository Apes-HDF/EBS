<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Tests\Api\ApiResource;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

final class GroupResourceTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    protected static ?bool $alwaysBootKernel = true;

    private const COUNT = TestReference::GROUP_COUNT;

    /**
     * @see GroupResource
     * @see GroupGetStatsProvider
     */
    public function testGroupResourceStats(): void
    {
        $client = self::createClient();
        $response = $client->request('GET', '/api/groups/stats');
        self::assertResponseHeaderSame('content-type', 'application/ld+json');
        self::assertJsonEquals([
            '@context' => '/api/contexts/Group',
            '@id' => '/api/groups/stats',
            '@type' => 'Group',
            'count' => self::COUNT,
        ]);
        $responseArray = $response->toArray();
        self::assertEqualsCanonicalizing(['@context', '@id', '@type', 'count'], array_keys($responseArray));
    }
}
