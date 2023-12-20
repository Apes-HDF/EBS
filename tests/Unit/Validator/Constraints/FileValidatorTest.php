<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints;

use App\Validator\Constraints\File;
use App\Validator\Constraints\FileValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<FileValidator>
 */
final class FileValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): FileValidator
    {
        return new FileValidator();
    }

    public function testDontvalidateString(): void
    {
        $this->validator->validate('foo', new File());
        $this->assertNoViolation();
    }

    public function testWithUploadedFile(): void
    {
        $image = realpath(__DIR__.'/../../../Fixtures/images/apes.png');
        $uploaddedDile = new UploadedFile((string) $image, 'apes.png');
        $this->validator->validate($uploaddedDile, new File());
        $this->buildViolation('The file could not be uploaded.')
            ->atPath('property.path')
            ->setCode('0')
            ->assertRaised();
    }
}
