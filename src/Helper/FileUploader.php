<?php

declare(strict_types=1);

namespace App\Helper;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class FileUploader
{
    /**
     * To upload a collection of images (as an array).
     *
     * @param array<UploadedFile> $images
     *
     * @return array<int, string>
     */
    public function uploadImageArray(FilesystemOperator $storage, array $images): array
    {
        $imagesUploaded = [];
        foreach ($images as $image) {
            $imagesUploaded[] = $this->uploadImage($storage, $image);
        }

        return $imagesUploaded;
    }

    /**
     * To upload a single image.
     */
    public function uploadImage(FilesystemOperator $storage, UploadedFile $image): string
    {
        $newFilename = Uuid::v4().'.'.$image->guessExtension();
        $storage->write($newFilename, $image->getContent());

        return $newFilename;
    }
}
