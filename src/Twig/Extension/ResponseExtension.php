<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\HttpFoundation\Response;
use Twig\Attribute\AsTwigFilter;

final class ResponseExtension
{
    #[AsTwigFilter(name: 'status_text')]
    public function getStatusText(int $errorCode): string
    {
        return Response::$statusTexts[$errorCode] ?? 'Unknown error code';
    }
}
