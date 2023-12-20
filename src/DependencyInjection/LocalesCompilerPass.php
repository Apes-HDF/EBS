<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Synchronize locales parameters.
 *
 * @see config/packages/framework.yaml
 * @see config/packages/translation.yaml
 */
final class LocalesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $enabledLocales = $container->getParameter('kernel.enabled_locales');
        $container->setParameter('requirements_locales', implode('|', $enabledLocales));
    }
}
