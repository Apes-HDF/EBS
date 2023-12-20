<?php

declare(strict_types=1);

namespace App\Message\Query\Security;

use Webmozart\Assert\Assert;

/**
 * @see GetUserByTokenQueryHandler
 */
final class GetUserByTokenQuery
{
    /**
     * The user email confirmation token (user.confirmationToken).
     */
    public string $token;

    public function __construct(string $token)
    {
        Assert::stringNotEmpty($token);
        $this->token = $token;
    }
}
