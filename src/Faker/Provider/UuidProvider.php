<?php

declare(strict_types=1);

namespace App\Faker\Provider;

use Faker\Provider\Base as BaseProvider;
use Symfony\Component\Uid\Uuid;

/**
 * Allows to always use the same uuid so the tests are easier to write and indempotent.
 */
final class UuidProvider extends BaseProvider
{
    public function uuid(?string $provided = null): Uuid
    {
        return $provided === null ? Uuid::v6() : Uuid::fromString($provided);
    }
}
