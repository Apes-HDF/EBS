<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\User\Group;

use App\Entity\UserGroup;
use App\Message\Command\User\Group\JoinGroupCommand;
use App\MessageHandler\Command\User\Group\JoinGroupCommandHandler;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class JoinGroupCommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    public function testAccessDeniedHttpException(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(JoinGroupCommandHandler::class);
        self::assertInstanceOf(JoinGroupCommandHandler::class, $handler);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Group is not public');

        $group = $this->getGroupRepository()->get(TestReference::GROUP_PRIVATE);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $message = new JoinGroupCommand($group->getId(), $user->getId());
        $handler($message);
    }

    /**
     * Can't join a paying group.
     */
    public function testIsChargedGroupException(): void
    {
        self::bootKernel();
        $this->fixDoctrine();
        $handler = self::getContainer()->get(JoinGroupCommandHandler::class);
        self::assertInstanceOf(JoinGroupCommandHandler::class, $handler);
        $group = $this->getGroupRepository()->get(TestReference::GROUP_1);
        $user = $this->getUserRepository()->get(TestReference::USER_11);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Group has paying offers, the user must pay');

        $message = new JoinGroupCommand($group->getId(), $user->getId());
        $handler($message);
        self::assertTrue($user->isMemberOf($group));
    }

    /**
     * No Doctrine error if the member is already member of the group.
     */
    public function testIsMemberOfNoError(): void
    {
        self::bootKernel();
        $this->fixDoctrine();
        $handler = self::getContainer()->get(JoinGroupCommandHandler::class);
        self::assertInstanceOf(JoinGroupCommandHandler::class, $handler);
        $group = $this->getGroupRepository()->get(TestReference::GROUP_5);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);

        $userGroup = (new UserGroup())
            ->setGroup($group)
            ->setUser($user)
            ->setMember();
        $group->addUserGroup($userGroup);
        $this->getUserGroupRepository()->save($userGroup, true);

        self::assertTrue($user->hasLink($group));
        self::assertTrue($user->isMemberOf($group));

        $message = new JoinGroupCommand($group->getId(), $user->getId());
        $handler($message);
        self::assertTrue($user->isMemberOf($group));
    }
}
