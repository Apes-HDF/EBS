<?php

declare(strict_types=1);

namespace App\Message\Query\Security;

use Webmozart\Assert\Assert;

/**
 * @see ResetPasswordQueryHandler
 */
final class ResetPasswordQuery
{
    public string $token;

    public function __construct(string $token)
    {
        Assert::stringNotEmpty($token);
        $this->token = $token;
    }
}
