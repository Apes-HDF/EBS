<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator as BaseFileValidator;

/**
 * This is a specific validator for Flysystem. In the string property we only store
 * the name of the file, not the path that is handled by Flysytem. So we don't validate
 * if is a string as the validation was already done on the UploadedFile object
 * when the form is sbumitted and before the image name is stored in the database.
 */
class FileValidator extends BaseFileValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (\is_string($value)) {
            return;
        }

        parent::validate($value, $constraint);
    }
}
