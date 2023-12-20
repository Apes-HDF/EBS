<?php

declare(strict_types=1);

namespace App\Search\Document;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Webmozart\Assert\Assert;

/**
 * DTO that represents a stored product document stored in Meilisearch. We use
 * a simple DTO to index only what we really need.
 */
final class ProductDocument
{
    public function __construct(
        /**
         * Uuid as a string.
         *
         * @see Product::$id
         */
        public readonly string $id,

        /**
         * User uuid as a string.
         *
         * @see User::$id
         */
        public readonly string $ownerId,

        /**
         * Type of product, service or object. Not searchable.
         *
         * @see Product::$type
         */
        public readonly string $type,

        /**
         * If the product is public or restricted to some groups.
         *
         * @see Product::$visibility
         */
        public readonly string $visibility,

        /**
         * @see Product::$name
         */
        public readonly string $name,

        /**
         * Categories' labels.
         *
         * @see Product::$category
         *
         * @var array<string>
         */
        public readonly array $categories,

        /**
         * Categories' IDs.
         *
         * @see Product::$category
         *
         * @var array<string>
         */
        public readonly array $categoriesIds,

        /**
         * Groups where the product is visible.
         *
         * @see Product::$groups
         *
         * @var array<string>
         */
        public readonly array $groupsIds,

        /**
         * Optional description.
         *
         * @see Product::$description
         */
        public readonly ?string $description,

        /**
         * Optional lat/long coordinates.
         *
         * @@see Product::$owner
         */
        public readonly ?GeoDocument $_geo,

        /**
         * When the document was indexed.
         */
        public readonly \DateTimeImmutable $indexedAt,
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        // categories
        $category = $product->getCategory();
        // Assert::isInstanceOf($category, Category::class); // category is not nullable in db
        $categoriesIds = [];
        $categories = $categoriesIds;
        $categories[] = $category->getName();
        $categoriesIds[] = (string) $category->getId();
        if ($category->hasParent()) {
            Assert::notNull($category->getParent());
            $categories[] = $category->getParent()->getName();
            $categoriesIds[] = (string) $category->getParent()->getId();
        }

        // add geoloc
        $owner = $product->getOwner();
        if ($owner->hasAddress()) {
            Assert::notNull($owner->getAddress());
            $geo = GeoDocument::fromAddress($owner->getAddress());
        }

        return new self(
            id: (string) $product->getId(),
            ownerId: (string) $product->getOwner()->getId(),
            type: $product->getType()->value,
            visibility: $product->getVisibility()->value,
            name: $product->getName(),
            categories: $categories,
            categoriesIds: $categoriesIds,
            groupsIds: $product->getGroupsIds(),
            description: $product->getDescription(),
            _geo: $geo ?? null,
            indexedAt: new \DateTimeImmutable()
        );
    }
}
