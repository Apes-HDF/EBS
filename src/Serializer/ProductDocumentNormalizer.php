<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Search\Document\ProductDocument;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class ProductDocumentNormalizer implements NormalizerInterface
{
    /**
     * @see https://github.com/symfony/symfony/discussions/47601
     */
    public function __construct(
        #[Autowire(service: ObjectNormalizer::class)]
        private readonly NormalizerInterface $normalizer
    ) {
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        // Meilisearch doesn't support null values for the _geo field for now
        // @see https://github.com/meilisearch/meilisearch/issues/3497
        // fixed in 1.1 to test without this fix
        if (\array_key_exists('_geo', $data) && $data['_geo'] === null) {
            unset($data['_geo']);
        }

        return $data;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof ProductDocument;
    }
}
