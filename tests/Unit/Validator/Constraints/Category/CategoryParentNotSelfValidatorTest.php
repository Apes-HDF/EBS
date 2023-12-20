<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints\Category;

use App\Entity\Category;
use App\Entity\User;
use App\Validator\Constraints\Category\CategoryParentNotSelf;
use App\Validator\Constraints\Category\CategoryParentNotSelfValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<CategoryParentNotSelfValidator>
 */
final class CategoryParentNotSelfValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): CategoryParentNotSelfValidator
    {
        return new CategoryParentNotSelfValidator();
    }

    public function testIsValid(): void
    {
        $this->validator->validate(new Category(), new CategoryParentNotSelf());
        $this->assertNoViolation();
    }

    public function testIsInvalid(): void
    {
        $group = new Category();
        $group->setParent($group);

        $this->validator->validate($group, new CategoryParentNotSelf());
        $this->buildViolation('validator.category.parentnotself')
            ->atPath('property.path.parent')
            ->assertRaised();
    }

    public function testInvalidValueType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new User(), new CategoryParentNotSelf());
    }

    public function testInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('foo', new Length(['max' => 5]));
    }
}
