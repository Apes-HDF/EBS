<?php

declare(strict_types=1);

namespace App\Test;

use App\DataFixtures\Processor\ValidationProcessor;
use App\Twig\CategoryExtension;
use App\Twig\Extension\EntityExtension;
use App\Twig\Extension\i18nExtension;
use App\Twig\FlysystemExtension;
use App\Twig\MenuExtension;
use App\Twig\TwigExtension;
use App\Twig\UserExtension;
use Psr\Container\ContainerInterface;

trait ContainerTrait
{
    /**
     * Quick fix for empty collection bug.
     *
     * @see https://stackoverflow.com/q/69937246/633864
     */
    public function fixDoctrineBug(ContainerInterface $container): void
    {
        $container->get('doctrine')->getManager()->clear();
    }

    public function getValidationProcessor(): ValidationProcessor
    {
        return self::getContainer()->get(ValidationProcessor::class);
    }

    public function getCategoryExtension(): CategoryExtension
    {
        return self::getContainer()->get(CategoryExtension::class);
    }

    public function getUserExtension(): UserExtension
    {
        return self::getContainer()->get(UserExtension::class);
    }

    public function getI18nExtension(): i18nExtension
    {
        return self::getContainer()->get(i18nExtension::class);
    }

    public function getTwigExtension(): TwigExtension
    {
        return self::getContainer()->get(TwigExtension::class);
    }

    public function getMenuExtension(): MenuExtension
    {
        return self::getContainer()->get(MenuExtension::class);
    }

    public function getFlysystemExtension(): FlysystemExtension
    {
        return self::getContainer()->get(FlysystemExtension::class);
    }

    public function getEntityExtension(): EntityExtension
    {
        return self::getContainer()->get(EntityExtension::class);
    }
}
