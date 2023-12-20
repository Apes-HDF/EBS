<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Controller\User\Account\DeleteUserAvatarAction;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see DeleteUserAvatarAction
 */
class DeleteUserAvatarActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const EDIT_ROUTE = '/en/my-account/user/'.TestReference::USER_16.'/delete-avatar';

    private const FLASH_SUCCESS = 'app.controller.user.account.delete_user_avatar_action.flash.success';

    public function testDeleteAvatar(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::EDIT_ROUTE);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
