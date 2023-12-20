<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\User\Group;

use App\Message\Command\User\Group\QuitGroupCommand;
use App\MessageHandler\Command\User\Group\QuitGroupCommandHandler;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class QuitGroupCommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    public function testUnprocessableEntityHttpException(): void
    {
        self::bootKernel();
        $this->fixDoctrine();
        $handler = self::getContainer()->get(QuitGroupCommandHandler::class);
        self::assertInstanceOf(QuitGroupCommandHandler::class, $handler);
        $this->expectException(UnprocessableEntityHttpException::class);

        $group = $this->getGroupRepository()->get(TestReference::GROUP_PRIVATE);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $message = new QuitGroupCommand($group->getId(), $user->getId(), null);
        $handler($message);
    }

    public function testQuitGroupWithAssociatedProductsAndSetPublic(): void
    {
        self::bootKernel();
        $this->fixDoctrine();
        $handler = self::getContainer()->get(QuitGroupCommandHandler::class);
        self::assertInstanceOf(QuitGroupCommandHandler::class, $handler);
        $group = $this->getGroupRepository()->get(TestReference::GROUP_1);
        $user = $this->getUserRepository()->get(TestReference::PLACE_APES);
        $message = new QuitGroupCommand($group->getId(), $user->getId(), 'public');
        $handler($message);
    }
}
