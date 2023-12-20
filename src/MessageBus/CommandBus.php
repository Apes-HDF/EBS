<?php

declare(strict_types=1);

namespace App\MessageBus;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $commandBus
    ) {
        $this->messageBus = $commandBus;
    }

    public function dispatch(object $query): mixed
    {
        return $this->handle($query);
    }
}
