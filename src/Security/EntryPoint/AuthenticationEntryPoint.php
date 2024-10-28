<?php

declare(strict_types=1);

namespace App\Security\EntryPoint;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

use function Symfony\Component\String\u;

/**
 * Simple entry point to prevent redirecting to the login page in case of a
 * JSON API call to a API Platform endpoint.
 *
 * @see ProductSwitchProcessorTest::testProductSwitchProcessorUnauthorizedFailure
 */
class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        /** @var string $route */
        $route = $request->attributes->get('_route');
        if ($authException instanceof InsufficientAuthenticationException && u($route)->startsWith('_api_')) {
            throw new UnauthorizedHttpException('', $authException->getMessage(), $authException);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
