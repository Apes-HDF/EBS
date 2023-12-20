<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints\Group;

use App\Entity\Group;
use App\Entity\User;
use App\Validator\Constraints\Group\GroupParentNotSelf;
use App\Validator\Constraints\Group\GroupParentNotSelfValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<GroupParentNotSelfValidator>
 */
final class GroupParentNotSelfValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): GroupParentNotSelfValidator
    {
        return new GroupParentNotSelfValidator();
    }

    public function testIsValid(): void
    {
        $this->validator->validate(new Group(), new GroupParentNotSelf());
        $this->assertNoViolation();
    }

    public function testIsInvalid(): void
    {
        $group = new Group();
        $group->setParent($group);

        $this->validator->validate($group, new GroupParentNotSelf());
        $this->buildViolation('validator.group.groupparentnotself')
            ->atPath('property.path.parent')
            ->assertRaised();
    }

    public function testInvalidValueType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new User(), new GroupParentNotSelf());
    }

    public function testInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('foo', new Length(['max' => 5]));
    }
}
