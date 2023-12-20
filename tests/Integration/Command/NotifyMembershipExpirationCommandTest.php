<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\NotifyMembershipExpirationCommand;
use App\Test\ContainerRepositoryTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class NotifyMembershipExpirationCommandTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(NotifyMembershipExpirationCommand::CMD);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'days' => 1,
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(sprintf('%d notification', 1), $output);
        self::assertStringContainsString('Groupe 1 of Camille', $output);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);

        // in one week
        $commandTester->execute([
            'days' => 7,
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(sprintf('%d notification', 1), $output);
        self::assertStringContainsString('Groupe 7 of Sarah', $output);
        self::assertEmailCount(2); // cumulative
        self::assertNotificationCount(2);
    }
}
