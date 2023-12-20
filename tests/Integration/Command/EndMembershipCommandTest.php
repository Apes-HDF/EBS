<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\EndMembershipCommand;
use App\Test\ContainerRepositoryTrait;
use App\Test\ContainerTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class EndMembershipCommandTest extends KernelTestCase
{
    use ContainerTrait;
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $this->fixDoctrineBug($kernel->getContainer());

        $application = new Application($kernel);
        $command = $application->find(EndMembershipCommand::CMD);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        self::assertEmailCount(1);
        self::assertNotificationCount(1);
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(sprintf('%d deletion', 1), $output);

        // already deleted
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        self::assertEmailCount(1); // not +1
        self::assertNotificationCount(1);
        $output = $commandTester->getDisplay();
        self::assertStringContainsString(sprintf('%d deletion', 0), $output);
    }
}
