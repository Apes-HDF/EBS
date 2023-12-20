<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Mailer\Email\EmailInterface;

/**
 * Gather all emails into an iterable.
 */
final class EmailCollection
{
    /**
     * @param iterable<EmailInterface> $emails
     */
    public function __construct(
        private readonly iterable $emails,
    ) {
    }

    /**
     * @return iterable<EmailInterface>
     */
    public function getEmails(): iterable
    {
        return $this->emails;
    }
}
