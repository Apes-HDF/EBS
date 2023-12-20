<?php

declare(strict_types=1);

namespace App\Doctrine\Manager;

use App\Controller\i18nTrait;
use App\Entity\Group;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Helper\FileUploader;
use App\Repository\ProductRepository;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductManager
{
    use i18nTrait;

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly TranslatorInterface $translator,
        private readonly FileUploader $fileUploader,
        private readonly FilesystemOperator $productStorage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->productRepository->save($entity, $flush);
    }

    /**
     * Duplicate product and handle translations.
     */
    public function duplicate(Product $product): Product
    {
        $duplicated = $product->duplicate();
        $duplicated->setName($this->translator->trans($this->getI18nPrefix().'.duplicate.copy_of').$product->getName());

        return $duplicated;
    }

    /**
     * @param array<UploadedFile>|null $images
     */
    public function multipleUpload(?array $images, Product $newProduct): void
    {
        if ($images !== null && \count($images) !== 0) {
            $imagesUploaded = $this->fileUploader->uploadImageArray($this->productStorage, $images);
            $newProduct->addImages($imagesUploaded);
        }
    }

    /**
     * Delete a photo in the db and in the storage.
     */
    public function deleteImage(Product $product, string $image): Product
    {
        try {
            $this->productStorage->delete($image);
        } catch (FilesystemException $e) {
            $this->logger->warning(sprintf('Unable to delete product (%s) image %s: %s', $product->getId(), $image, $e->getMessage()));
        }

        $product->deleteImage($image);
        $this->save($product, true);

        return $product;
    }

    public function hasProductsOnlyInGroup(Group $group, ?User $user): bool
    {
        // not logged
        if ($user === null) {
            return false;
        }

        // check the products published in this specific group
        $productsQuery = $this->productRepository->getUserProductsByType($user, null, null, $group);
        /** @var array<Product> $products */
        $products = $productsQuery->execute();

        // no product, so nothing to check
        if (\count($products) === 0) {
            return false;
        }

        // now check if those products are published in other groups than this one
        // as we already know the product is published in at least one group, we
        // just have to check that the count in greater than 1 (the group + at least
        // another one)
        foreach ($products as $product) {
            if ($product->getGroups()->count() > 1) {
                return false;
            }
        }

        return true;
    }

    public function initObject(User $user): Product
    {
        return (new Product())
            ->setOwner($user)
            ->setType(ProductType::OBJECT)
            ->setStatus(ProductStatus::ACTIVE);
    }

    public function initService(User $user): Product
    {
        return (new Product())
            ->setOwner($user)
            ->setType(ProductType::SERVICE)
            ->setStatus(ProductStatus::ACTIVE);
    }
}
