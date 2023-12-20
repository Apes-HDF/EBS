<?php

declare(strict_types=1);

namespace App\MessageBus;

interface QueryBusInterface
{
    public function query(object $query): mixed;
}
