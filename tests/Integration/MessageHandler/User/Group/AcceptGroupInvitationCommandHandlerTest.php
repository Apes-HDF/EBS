<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\User\Group;

use App\Message\Command\User\Group\AcceptGroupInvitationCommand;
use App\MessageHandler\Command\User\Group\AcceptGroupInvitationCommandHandler;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class AcceptGroupInvitationCommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    public function testUnprocessableEntityHttpException(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(AcceptGroupInvitationCommandHandler::class);
        self::assertInstanceOf(AcceptGroupInvitationCommandHandler::class, $handler);
        $this->expectException(UnprocessableEntityHttpException::class);

        $group = $this->getGroupRepository()->get(TestReference::GROUP_PRIVATE);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $message = new AcceptGroupInvitationCommand($group->getId(), $user->getId());
        $handler($message);
    }
}
