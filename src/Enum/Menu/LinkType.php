<?php

declare(strict_types=1);

namespace App\Enum\Menu;

use App\Enum\AsArrayTrait;

enum LinkType: string
{
    use AsArrayTrait;

    case LINK = 'link';

    case SOCIAL_NETWORK = 'social_network';
}
