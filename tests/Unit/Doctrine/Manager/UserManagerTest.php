<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine\Manager;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Helper\FileUploader;
use App\Helper\StringHelper;
use App\Repository\UserRepository;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

final class UserManagerTest extends TestCase
{
    public function testDeleteAvatarException(): void
    {
        $exception = new UnableToDeleteFile('foo');
        $userStorageMock = $this->getMockBuilder(FilesystemOperator::class)->disableOriginalConstructor()->getMock();
        $userStorageMock->method('delete')->willThrowException($exception);

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        // test that the logger was called buut no error 500 should be raised
        $loggerMock->expects(self::once())
            ->method('warning');

        $userManager = new UserManager(
            $this->getMockBuilder(UserPasswordHasherInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ClockInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(StringHelper::class)->disableOriginalConstructor()->getMock(),
            $userStorageMock,
            $this->getMockBuilder(FileUploader::class)->disableOriginalConstructor()->getMock(),
            $loggerMock
        );
        $user = (new User())
            ->setId(Uuid::v6())
            ->setAvatar('foobar.png')
        ;
        $userManager->deleteAvatar($user);
    }
}
