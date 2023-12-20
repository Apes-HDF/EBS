<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine\Manager;

use App\Doctrine\Manager\ProductManager;
use App\Entity\Product;
use App\Helper\FileUploader;
use App\Repository\ProductRepository;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductManagerTest extends TestCase
{
    /**
     * @see issue #585 in historic repo
     */
    public function testDeleteImageException(): void
    {
        $productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploaderMock = $this->getMockBuilder(FileUploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // test that the logger was called buut no error 500 should be raised
        $loggerMock->expects(self::once())
            ->method('warning');

        $productStorageMock = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exception = new UnableToDeleteFile('foo');
        $productStorageMock->method('delete')->willThrowException($exception);
        $productManager = new ProductManager(
            $productRepositoryMock,
            $translatorMock,
            $fileUploaderMock,
            $productStorageMock,
            $loggerMock
        );
        $product = (new Product())
            ->setId(Uuid::v6());
        $productManager->deleteImage($product, 'foobar.png');
    }
}
