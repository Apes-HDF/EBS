<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\Constraints\MenuItem;

use App\Entity\MenuItem;
use App\Entity\User;
use App\Validator\Constraints\MenuItem\MenuItemParentNotSelf;
use App\Validator\Constraints\MenuItem\MenuItemParentNotSelfValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<MenuItemParentNotSelfValidator>
 */
final class MenuItemParentNotSelfValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): MenuItemParentNotSelfValidator
    {
        return new MenuItemParentNotSelfValidator();
    }

    public function testIsValid(): void
    {
        $this->validator->validate(new MenuItem(), new MenuItemParentNotSelf());
        $this->assertNoViolation();
    }

    public function testIsInvalid(): void
    {
        $MenuItem = new MenuItem();
        $MenuItem->setParent($MenuItem);

        $this->validator->validate($MenuItem, new MenuItemParentNotSelf());
        $this->buildViolation('validator.menuitem.parentnotself')
            ->atPath('property.path.parent')
            ->assertRaised();
    }

    public function testInvalidValueType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new User(), new MenuItemParentNotSelf());
    }

    public function testInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('foo', new Length(['max' => 5]));
    }
}
