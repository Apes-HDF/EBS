<?php

declare(strict_types=1);

namespace App\Enum\Group;

use App\Controller\i18nTrait;
use App\Enum\AsArrayTrait;

enum GroupType: string
{
    use AsArrayTrait;
    use i18nTrait;

    case PUBLIC = 'public';
    case PRIVATE = 'private';

    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }

    public function getTranskey(): string
    {
        return $this->getI18nPrefix().'.'.$this->value;
    }
}
