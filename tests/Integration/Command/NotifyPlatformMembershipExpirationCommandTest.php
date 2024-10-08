<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\NotifyPlatformMembershipExpirationCommand;
use App\Test\ContainerRepositoryTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class NotifyPlatformMembershipExpirationCommandTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        // temporarily set global configuration as globalPaidMembership = true
        $configuration = $this->getConfigurationRepository()->getInstanceConfigurationOrCreate();
        $newConfig = $configuration->getConfiguration();
        $newConfig['global']['globalPaidMembership'] = true;
        $configuration->setConfiguration($newConfig);
        $this->getConfigurationRepository()->save($configuration, true);

        $application = new Application($kernel);
        $command = $application->find(NotifyPlatformMembershipExpirationCommand::CMD);
        $commandTester = new CommandTester($command);

        // in one week
        $commandTester->execute([
            'days' => 7,
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(\sprintf('%d notification', 1), $output);
        self::assertStringContainsString(\sprintf('notifying platform membership expiration for user %s', 'Kevin'), $output);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);
    }
}
