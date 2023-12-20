<?php

declare(strict_types=1);

namespace App\MessageBus;

interface CommandBusInterface
{
    public function dispatch(object $query): mixed;
}
