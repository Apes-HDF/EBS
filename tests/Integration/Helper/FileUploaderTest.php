<?php

declare(strict_types=1);

namespace App\Tests\Integration\Helper;

use App\Helper\FileUploader;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileUploaderTest extends KernelTestCase
{
    /**
     * Complete code cov.
     */
    public function testUpload(): void
    {
        self::bootKernel();
        /** @var FilesystemOperator $storage */
        $storage = self::getContainer()->get('product.storage');
        $helper = new FileUploader();
        $imageName1 = 'apes.png';
        $imageName2 = 'apes.png';

        $image1 = realpath(__DIR__.'/../../Fixtures/images/'.$imageName1);
        $uploadedFile1 = new UploadedFile((string) $image1, $imageName1);

        $image2 = realpath(__DIR__.'/../../Fixtures/images/'.$imageName2);
        $uploadedFile2 = new UploadedFile((string) $image2, $imageName2);

        $newFilesName = $helper->uploadImageArray($storage, [$uploadedFile1, $uploadedFile2]);

        self::assertTrue($storage->fileExists($newFilesName[0]));
        self::assertTrue($storage->fileExists($newFilesName[1]));
    }
}
