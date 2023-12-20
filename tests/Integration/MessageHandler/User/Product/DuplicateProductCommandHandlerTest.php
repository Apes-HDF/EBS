<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\User\Product;

use App\Message\Command\User\Product\DuplicateProductCommand;
use App\MessageHandler\Command\Product\DuplicateProductCommandHandler;
use App\Security\Voter\ProductVoter;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final class DuplicateProductCommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    public function testAccessDeniedException(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(DuplicateProductCommandHandler::class);
        self::assertInstanceOf(DuplicateProductCommandHandler::class, $handler);
        $this->expectException(AccessDeniedException::class);
        $message = new DuplicateProductCommand(Uuid::fromString(TestReference::SERVICE_USER_16_1), ProductVoter::DUPLICATE);
        $handler($message);
    }
}
