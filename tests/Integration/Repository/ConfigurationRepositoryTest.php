<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Configuration;
use App\Enum\ConfigurationType;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ConfigurationRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::CONFIGURATION_COUNT;

    /**
     * Better code cov.
     */
    public function testConfigurationRepository(): void
    {
        self::bootKernel();
        $repo = $this->getConfigurationRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $configuration = new Configuration();
        $configuration->setType(ConfigurationType::INSTANCE);
        $repo->save($configuration, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($configuration, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        /** @var Configuration $fixtureCfg */
        $fixtureCfg = $repo->getInstanceConfiguration();
        $repo->remove($fixtureCfg, true);
        $repo->getInstanceConfigurationOrCreate();
        $count = $repo->count([]);
        self::assertSame(0, $count);
    }
}
