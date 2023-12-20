<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\LocalesCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Main application kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Keep main services.yaml clean.
     */
    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = $this->getConfigDir();

        // packages
        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');

        // services
        $container->import($configDir.'/services.yaml');
        $container->import($configDir.'/{services}_'.$this->environment.'.yaml');

        // custom extra configuration for packages
        $container->import($configDir.'/{packages_extra}/*.{php,yaml}');
    }

    /**
     * Additional container stuff.
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new LocalesCompilerPass(), PassConfig::TYPE_OPTIMIZE);
    }
}
