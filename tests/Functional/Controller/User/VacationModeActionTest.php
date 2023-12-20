<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Controller\User\VacationModeAction;
use App\Test\KernelTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** @see VacationModeAction */
class VacationModeActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    public const ROUTE = '/fr/mon-compte/mode-vacances';

    private const FLASH_SUCCESS = 'app.controller.user.vacation_mode_action.flash.success';

    public function testVacationMode(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
