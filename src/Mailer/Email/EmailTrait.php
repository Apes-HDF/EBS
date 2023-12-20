<?php

declare(strict_types=1);

namespace App\Mailer\Email;

trait EmailTrait
{
    /**
     * @param class-string $code
     */
    public function supports(string $code): bool
    {
        return $code === self::class;
    }
}
