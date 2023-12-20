<?php

declare(strict_types=1);

namespace App\Flysystem;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Generate generic callbacks for Flysystem configuration in EasyAdmin CRUD controllers.
 * The storage class is generic so it can be used with storage specific services.
 *
 * @see AbstractCategoryCrudController
 */
final class EasyAdminHelper
{
    /**
     * Callack used when uploading a file.
     */
    public function getUploadNewCallback(FilesystemOperator $storage): callable
    {
        return static function (UploadedFile $file, string $uploadDir, string $fileName) use ($storage) {
            $storage->write($fileName, $file->getContent());
        };
    }

    /**
     * Callack used when deleting a file.
     */
    public function getUploadDeleteCallback(FilesystemOperator $storage): callable
    {
        return static function (File $file) use ($storage) {
            $storage->delete($file->getFilename());
        };
    }
}
