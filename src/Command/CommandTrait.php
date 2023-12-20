<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command helpers.
 */
trait CommandTrait
{
    /**
     * Get a valid page number that is equal or greater than one.
     */
    public function memoryReport(SymfonyStyle $io): void
    {
        $io->info('Memory: '.round(memory_get_usage() / 1024 / 1024, 2)." mb\n");
    }

    private function done(SymfonyStyle $io): void
    {
        $io->success('DONE');
    }

    protected function configureCommand(string $description): void
    {
        [$desc, $class] = [$description, $this::class];
        $this
            ->setHelp(
                <<<EOT
$desc

COMMAND:
<comment>$class</comment>

DEV:
<info>%command.full_name% -vv</info>

PROD:
<info>%command.full_name% --env=prod --no-debug</info>
EOT
            );
    }
}
