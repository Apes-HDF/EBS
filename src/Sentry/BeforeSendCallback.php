<?php

declare(strict_types=1);

namespace App\Sentry;

use Sentry\Event;
use Sentry\EventHint;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Filtre les événements envoyés à Sentry : les erreurs "client" (HTTP 4xx, accès refusé,
 * authentification) ne sont pas des bugs applicatifs et ne doivent pas remonter.
 */
final class BeforeSendCallback
{
    public function __invoke(Event $event, ?EventHint $hint): ?Event
    {
        $exception = $hint?->exception;

        if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500) {
            return null;
        }

        if ($exception instanceof AccessDeniedException || $exception instanceof AuthenticationException) {
            return null;
        }

        return $event;
    }
}
