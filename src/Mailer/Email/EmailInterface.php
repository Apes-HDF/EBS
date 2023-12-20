<?php

declare(strict_types=1);

namespace App\Mailer\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

interface EmailInterface
{
    /**
     * Test if the email can handle the code and context.
     *
     * @param class-string $code
     */
    public function supports(string $code): bool;

    /**
     * Get the email to send by the mailer service.
     *
     * @param array<string, mixed> $context
     */
    public function getEmail(array $context): TemplatedEmail;
}
