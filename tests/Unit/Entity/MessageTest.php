<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Enum\Message\MessageType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class MessageTest extends TestCase
{
    public function testMessage(): void
    {
        $message = new Message();
        $id = Uuid::v6();
        $serviceRequest = new ServiceRequest();
        self::assertSame($id, $message->setId($id)->getId());
        self::assertSame(MessageType::SYSTEM, $message->setType(MessageType::SYSTEM)->getType());
        self::assertSame($serviceRequest, $message->setServiceRequest($serviceRequest)->getServiceRequest());
        self::assertSame('foobar', $message->setMessage('foobar')->getMessage());
        self::assertSame('foobar', (string) $message);
        self::assertNull($message->getMessageTemplate());
        self::assertEmpty($message->getMessageParameters());

        self::assertSame(MessageType::FROM_OWNER, $message->setType(MessageType::FROM_OWNER)->getType());
        self::assertTrue($message->getType()->isFromOwner());
        self::assertFalse($message->getType()->isFromRecipient());

        self::assertSame(MessageType::FROM_RECIPIENT, $message->setType(MessageType::FROM_RECIPIENT)->getType());
        self::assertFalse($message->getType()->isFromOwner());
        self::assertTrue($message->getType()->isFromRecipient());

        self::assertFalse($message->isOwnerRead());
        self::assertNull($message->getOwnerReadAt());

        self::assertFalse($message->isRecipientRead());
        self::assertNull($message->getRecipientReadAt());
    }

    public function testMessageGetRecipient(): void
    {
        $message = new Message();
        $serviceRequest = new ServiceRequest();
        $message->setServiceRequest($serviceRequest);
        $owner = new User();
        $recipent = new User();
        $serviceRequest->setOwner($owner)->setRecipient($recipent);

        $message->setType(MessageType::FROM_OWNER);
        self::assertSame($owner, $message->getSender());
        self::assertSame($recipent, $message->getRecipient());

        $message->setType(MessageType::FROM_RECIPIENT);
        self::assertSame($recipent, $message->getSender());
        self::assertSame($owner, $message->getRecipient());
    }

    public function testMessageGetSenderException(): void
    {
        $message = new Message();
        self::assertSame(MessageType::SYSTEM, $message->setType(MessageType::SYSTEM)->getType());
        $this->expectException(\LogicException::class);
        $message->getSender();
    }

    public function testMessageGetRecipientException(): void
    {
        $message = new Message();
        self::assertSame(MessageType::SYSTEM, $message->setType(MessageType::SYSTEM)->getType());
        $this->expectException(\LogicException::class);
        $message->getRecipient();
    }
}
