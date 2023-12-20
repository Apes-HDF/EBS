<?php

declare(strict_types=1);

namespace App\Enum\Message;

use App\Enum\AsArrayTrait;

/**
 * Allows to identity the source of a given message.
 */
enum MessageType: string
{
    use AsArrayTrait;

    /**
     * Message sent by the the system. es: the load is now finished.
     */
    case SYSTEM = 'system';

    /**
     * Message has been sent by the owner/provider of the product/service.
     */
    case FROM_OWNER = 'from_owner';

    /**
     * Message from the borrower/recipient of the product/service.
     */
    case FROM_RECIPIENT = 'from_recipient';

    public function isSystem(): bool
    {
        return $this === self::SYSTEM;
    }

    public function isFromOwner(): bool
    {
        return $this === self::FROM_OWNER;
    }

    public function isFromRecipient(): bool
    {
        return $this === self::FROM_RECIPIENT;
    }
}
