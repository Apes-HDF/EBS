<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use Symfony\Component\Panther\PantherTestCase;

final class AppControllerTest extends PantherTestCase
{
    /**
     * @see AppController::home()
     */
    public function testHome(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/fr/accueil');
        // $client->takeScreenshot('var/screen.jpg'); // the screenshot is stored at the root of the project
        self::assertSelectorTextContains('body', 'APES Hauts-de-France');
    }
}
