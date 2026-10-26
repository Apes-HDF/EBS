<?php

declare(strict_types=1);

namespace App\Message\Command\Security;

use Symfony\Component\Uid\Uuid;

final class AccountCreateStep2RefreshCommand
{
    public function __construct(public Uuid $id)
    {
    }
}
