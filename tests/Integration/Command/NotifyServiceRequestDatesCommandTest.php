<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\NotifyServiceRequestDatesCommand;
use App\Test\ContainerRepositoryTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class NotifyServiceRequestDatesCommandTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(NotifyServiceRequestDatesCommand::CMD);
        $commandTester = new CommandTester($command);

        // start notification
        $commandTester->execute([
            'mode' => 'start',
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(\sprintf('%d notification(s)', 2), $output); // owner + recipient
        self::assertStringContainsString('DONE', $output);
        self::assertEmailCount(2);
        self::assertNotificationCount(2);

        // end notification
        $commandTester->execute([
            'mode' => 'end',
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(\sprintf('%d notification(s)', 2), $output);
        self::assertStringContainsString('DONE', $output);
        self::assertEmailCount(4); // cumulative results
        self::assertNotificationCount(4);
    }
}
