<?php

declare(strict_types=1);

namespace App\Message\Command\User\ServiceRequest;

use Symfony\Component\Uid\Uuid;

/**
 * @see ConversationController
 * @see TryAutoFinalizeCommandHandler
 */
final class TryAutoFinalizeCommand
{
    public function __construct(
        // related service request
        public readonly Uuid $requestServiceId,
    ) {
    }
}
