<?php

declare(strict_types=1);

namespace App\Tests\Api\State\Processor;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ProductSwitchProcessor
 */
final class ProductSwitchProcessorTest extends ApiTestCase
{
    use ReloadDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const API_URL = '/api/product/'.TestReference::OBJECT_LOIC_1.'/switchStatus';

    /**
     * User not logged.
     */
    public function testProductSwitchProcessorUnauthorizedFailure(): void
    {
        $client = self::createClient();
        $client->request('PATCH', self::API_URL);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertJsonContains([
            '@context' => '/api/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Full authentication is required to access this resource.',
        ]);
    }

    /**
     * Incorrect uuid.
     */
    public function testProductSwitchProcessorNotFoundFailure(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('PATCH', '/api/product/'.TestReference::UUID_404.'/switchStatus');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Object of someone else.
     */
    public function testProductSwitchProcessorForbiddenFailure(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('PATCH', self::API_URL);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Nominal case.
     */
    public function testProductSwitchProcessorSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // check initial state
        $product = $this->getProductRepository()->get(TestReference::OBJECT_LOIC_1);
        self::assertSame($product->getStatus()->value, 'active');

        // switch
        $response = $client->request('PATCH', self::API_URL);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $responseArray = $response->toArray();
        self::assertSame($responseArray['status'], 'paused');

        // another switch
        $response = $client->request('PATCH', self::API_URL);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $responseArray = $response->toArray();
        self::assertSame($responseArray['status'], 'active');
    }
}
