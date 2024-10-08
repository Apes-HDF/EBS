<?php

declare(strict_types=1);

namespace App\Message\Command\User\ServiceRequest;

use Symfony\Component\Uid\Uuid;

/**
 * @see NewMessageType
 * @see CreateMessageCommandHandler
 */
final class CreateMessageCommand
{
    public function __construct(
        // related service request
        public readonly Uuid $requestServiceId,

        // user who send the message
        public readonly Uuid $senderId,

        // content of the message
        public string $message,
    ) {
    }
}
