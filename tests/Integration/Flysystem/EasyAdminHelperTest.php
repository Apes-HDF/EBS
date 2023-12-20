<?php

declare(strict_types=1);

namespace App\Tests\Integration\Flysystem;

use App\Flysystem\EasyAdminHelper;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

final class EasyAdminHelperTest extends KernelTestCase
{
    public function testEasyAdminHelper(): void
    {
        self::bootKernel();
        /** @var FilesystemOperator $storage */
        $storage = self::getContainer()->get('category.storage');
        $helper = new EasyAdminHelper();
        $imageName = 'apes.png';

        $image = (string) realpath(__DIR__.'/../../Fixtures/images/'.$imageName);

        $storage->write($imageName, (string) file_get_contents($image));
        self::assertTrue($storage->fileExists($imageName));

        $file = new File($image);
        $callback = $helper->getUploadDeleteCallback($storage);
        $callback($file);

        self::assertFalse($storage->fileExists($imageName));
    }
}
