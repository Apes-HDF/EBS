<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Response;

final class AppControllerTest extends WebTestCase
{
    /**
     * @see AppController::root()
     */
    public function testRoot(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirects('/fr');
    }

    /**
     * @see AppController::home()
     */
    public function testHome(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr');
        self::assertResponseIsSuccessful();
    }

    /**
     * @see AppController::ping()
     */
    public function testPing(): void
    {
        $client = self::createClient();
        $client->request('GET', '/ping');
        self::assertResponseIsSuccessful();
    }

    /**
     * @see ErrorHandler::handleException
     */
    public function test404(): void
    {
        $client = self::createClient();
        $client->request('GET', '/404');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
