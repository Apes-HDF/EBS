<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Force the CLI output for the dd() and dump() functions. Useful when doing CURL
 * calls in the terminal and wanting to debug.
 */
final class VarDumperHelper
{
    public static function forceCli(): void
    {
        CliDumper::$defaultOutput = 'php://output';
        VarDumper::setHandler(function ($var) {
            $cloner = new VarCloner();
            $dumper = new CliDumper();
            $dumper->dump($cloner->cloneVar($var));
        });
    }
}
