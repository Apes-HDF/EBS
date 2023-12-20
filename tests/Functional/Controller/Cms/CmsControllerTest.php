<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Cms;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see CmsController::page()
 */
final class CmsControllerTest extends WebTestCase
{
    public function testPageSuccess(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr/qui-sommes-nous');
        self::assertResponseIsSuccessful();
    }

    public function testPageNotFound(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr/foobar');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Error case when asking for a not enabled locale.
     *
     * @see config/packages/framework.yaml
     */
    public function testPageLocaleNotEnabled(): void
    {
        $client = self::createClient();
        $client->request('GET', '/de/qui-sommes-nous');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
