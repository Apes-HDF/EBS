<?php

declare(strict_types=1);

namespace App\Message\Command\User\ServiceRequest;

use Symfony\Component\Uid\Uuid;

/**
 * @see ConversationController
 * @see ReadMessagesCommandCommandHandler
 */
final class ReadMessagesCommand
{
    public function __construct(
        // related service request
        public readonly Uuid $requestServiceId,

        // user who read the messages
        public readonly Uuid $readerId,
     ) {
    }
}
