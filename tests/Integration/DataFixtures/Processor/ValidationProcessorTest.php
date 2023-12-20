<?php

declare(strict_types=1);

namespace App\Tests\Integration\DataFixtures\Processor;

use App\Entity\Group;
use App\Test\ContainerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ValidationProcessorTest extends KernelTestCase
{
    use ContainerTrait;

    public function testPreProcessException(): void
    {
        self::bootKernel();
        $validationProcessor = $this->getValidationProcessor();
        $group = new Group(); // a group must have a name
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Error when validating fixture \"my_wrong_fixture\"/');
        $this->expectExceptionMessageMatches('/This value should not be blank/');
        $validationProcessor->preProcess('my_wrong_fixture', $group);
    }
}
