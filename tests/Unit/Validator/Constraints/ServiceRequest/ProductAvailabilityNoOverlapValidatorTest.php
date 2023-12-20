<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints\ServiceRequest;

use App\Entity\Product;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Validator\Constraints\ServiceRequest\ProductAvailabilityNoOverlap;
use App\Validator\Constraints\ServiceRequest\ProductAvailabilityNoOverlapValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ProductAvailabilityNoOverlapValidator>
 */
final class ProductAvailabilityNoOverlapValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ProductAvailabilityNoOverlapValidator
    {
        return new ProductAvailabilityNoOverlapValidator();
    }

    public function testIsValid(): void
    {
        $sr = (new ServiceRequest())
            ->setProduct(new Product())
            ->setStartAt(new \DateTimeImmutable('today'))
            ->setEndAt(new \DateTimeImmutable('tomorrow'))
        ;
        $this->validator->validate($sr, new ProductAvailabilityNoOverlap());
        $this->assertNoViolation();
    }

    public function testInvalidValueType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new User(), new ProductAvailabilityNoOverlap());
    }

    public function testInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('foo', new Length(['max' => 5]));
    }
}
