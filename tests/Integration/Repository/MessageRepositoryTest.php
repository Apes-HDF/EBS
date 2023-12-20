<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Message;
use App\Enum\Message\MessageType;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MessageRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::MESSAGES_COUNT;

    public function testMessageRepository(): void
    {
        self::bootKernel();
        $repo = $this->getMessageRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $message = new Message();
        $message->setServiceRequest($this->getServiceRequestRepository()->get(TestReference::SERVICE_REQUEST_1));
        $message->setType(MessageType::FROM_OWNER);
        $message->setMessage('foobar');

        $repo->save($message, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($message, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
