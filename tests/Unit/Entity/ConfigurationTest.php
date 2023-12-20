<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Configuration;
use App\Enum\ConfigurationType;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testConfiguration(): void
    {
        $configuration = new Configuration();
        self::assertNull($configuration->getId());
        self::assertSame(ConfigurationType::INSTANCE, $configuration->setType(ConfigurationType::INSTANCE)->getType());
    }
}
