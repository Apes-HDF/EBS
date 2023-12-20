<?php

declare(strict_types=1);

namespace App\Flysystem;

use App\Controller\Admin\DashboardController;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\Translation\t;

/**
 * Some generic helper to use the same validation in all the application.
 *
 * @see AbstractProductCrudController
 */
final class MediaManager
{
    /**
     * For translation messages.
     */
    public string $uploadImagesAllowedExtensionsMsg;

    public const MAX_PHOTO_COUNT = 5;

    /**
     * @param array<string> $uploadImagesAllowedExtensions
     */
    public function __construct(
        #[Autowire('%upload_images_allowed_extensions%')]
        public readonly array $uploadImagesAllowedExtensions,
        #[Autowire('%upload_maxsize_by_file%')]
        public readonly int $uploadMaxsizeByFile,
    ) {
        $this->uploadImagesAllowedExtensionsMsg = implode(', ', $uploadImagesAllowedExtensions);
    }

    /**
     * To validate a property containing a single image.
     *
     * @see https://symfony.com/doc/current/reference/constraints/File.html
     */
    public function getFileConstraints(?string $validationGroup = null): AppAssert\File
    {
        return new AppAssert\File(
            maxSize: $this->uploadMaxsizeByFile.'mi',
            groups: $validationGroup !== null ? [$validationGroup] : null,
            extensions: $this->uploadImagesAllowedExtensions,
            extensionsMessage: 'validator.extensions_message',
        );
    }

    /**
     * To validate a property containing a collection of images (as an array).
     *
     * @see https://symfony.com/doc/current/reference/constraints/All.html
     */
    public function getImageArrayConstraints(?string $validationGroup = null): Assert\All
    {
        return new Assert\All([
            $this->getFileConstraints($validationGroup),
        ]);
    }

    /**
     * Help message that displays the allowed extension and the maximum size by
     * image.
     */
    public function getHelpMessage(): TranslatableMessage
    {
        return
            t('images.help', [
                '%extensions%' => $this->uploadImagesAllowedExtensionsMsg,
                '%upload_maxsize_by_file%' => $this->uploadMaxsizeByFile,
            ], DashboardController::DOMAIN);
    }
}
