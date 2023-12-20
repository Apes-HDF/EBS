<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Test\ContainerTrait;
use App\Tests\Unit\Entity\DummyImage;
use App\Tests\Unit\Entity\DummyImages;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FlysystemExtensionTest extends KernelTestCase
{
    use ContainerTrait;

    /**
     * Better coverage.
     */
    public function testGetPublicUrlSuccess(): void
    {
        self::bootKernel();
        $flysystemExtension = $this->getFlysystemExtension();
        $category = new Category();
        $name = 'apes.png';
        $category->setImage($name);
        $publicUrl = $flysystemExtension->getPublicUrl($category);
        self::assertSame('/storage/uploads/category/apes.png', $publicUrl);

        $user = (new User())->setAvatar($name);
        $publicUrl = $flysystemExtension->getPublicUrl($user);
        self::assertSame('/storage/uploads/user/apes.png', $publicUrl);

        $product = (new Product())->setImages([$name]);
        $publicUrlImage = $flysystemExtension->getPublicUrlImage($product, $name);
        self::assertSame('/storage/uploads/product/apes.png', $publicUrlImage);
    }

    public function testGetPublicUrlException(): void
    {
        self::bootKernel();
        $flysystemExtension = $this->getFlysystemExtension();
        $dummyImage = new DummyImage();
        $this->expectException(\LogicException::class);
        $flysystemExtension->getPublicUrl($dummyImage);
    }

    public function testGetPublicUrlImageException(): void
    {
        self::bootKernel();
        $flysystemExtension = $this->getFlysystemExtension();
        $dummyImages = new DummyImages();
        $this->expectException(\LogicException::class);
        $flysystemExtension->getPublicUrlImage($dummyImages, 'foo.png');
    }
}
