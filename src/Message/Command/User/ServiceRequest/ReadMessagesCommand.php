<?php

declare(strict_types=1);

namespace App\Message\Command\User\ServiceRequest;

use Symfony\Component\Uid\Uuid;

/**
 * @see ConversationController
 * @see ReadMessagesCommandCommandHandler
 */
final readonly class ReadMessagesCommand
{
    public function __construct(
        // related service request
        public Uuid $requestServiceId,

        // user who read the messages
        public Uuid $readerId,
    ) {
    }
}
