<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\File as BaseFile;

/**
 * This is a custom File constraint for Flysystem.
 *
 * @see FileValidator
 * @see Product::$images
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class File extends BaseFile
{
}
