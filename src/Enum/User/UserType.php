<?php

declare(strict_types=1);

namespace App\Enum\User;

use App\Enum\AsArrayTrait;

enum UserType: string
{
    use AsArrayTrait;

    case ADMIN = 'admin'; // can access the administration section
    case USER = 'user'; // standard user
    case PLACE = 'place'; // special user that have a public associated address (shop, association...)

    /**
     * @return array<string,string>
     */
    public static function getForFront(): array
    {
        return [
            self::USER->name => self::USER->value,
            self::PLACE->name => self::PLACE->value,
        ];
    }
}
