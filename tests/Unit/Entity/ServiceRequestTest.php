<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\Product;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Enum\Product\ProductType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ServiceRequestTest extends TestCase
{
    public function testServiceRequest(): void
    {
        $sr = new ServiceRequest();
        $id = Uuid::v6();
        self::assertSame($id, $sr->setId($id)->getId());

        $message = new Message();
        $id = Uuid::v6();
        self::assertSame($id, $message->setId($id)->getId());

        $product = new Product();
        $product->setType(ProductType::OBJECT);
        $sr->setProduct($product);
        self::assertTrue($sr->isLoan());

        $product->setType(ProductType::SERVICE);
        self::assertTrue($sr->isService());

        $sr->setRecipient(new User());
        self::assertFalse($sr->isRecipient(new User()));

        $sr->setOwner(new User());
        self::assertFalse($sr->isOwner(new User()));

        self::assertCount(0, $sr->getMessages());
        self::assertSame(0, $sr->messagesCount());

        $sr->addMessage($message);
        self::assertCount(1, $sr->getMessages());
        self::assertSame(1, $sr->messagesCount());

        $sr->removeMessage($message);
        self::assertCount(0, $sr->getMessages());
        self::assertSame(0, $sr->messagesCount());

        $collection = new ArrayCollection();
        $collection->add($message);

        $sr->setMessages($collection);
        self::assertCount(1, $sr->getMessages());
        self::assertSame(1, $sr->messagesCount());
    }

    /**
     * In the functional tests. The message collection is empty and therefore the
     * hasUnreadMessages function is not tested correctly.
     */
    public function testServiceRequestHasUnreadMessages(): void
    {
        $sr = new ServiceRequest();
        $owner = new User();
        $sr->setOwner($owner);

        $message = new Message();
        $message->setOwnerRead(false);
        $sr->addMessage($message);
        self::assertTrue($sr->hasUnreadMessages($owner));
        $message->setOwnerRead(true);
        self::assertFalse($sr->hasUnreadMessages($owner));

        $recipient = new User();
        $sr->setRecipient($recipient);
        self::assertTrue($sr->hasUnreadMessages($recipient));
        $message->setRecipientRead(true);
        self::assertFalse($sr->hasUnreadMessages($recipient));
    }
}
