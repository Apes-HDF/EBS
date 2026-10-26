<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Command\SetUserPasswordCommand;
use App\Entity\User;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SetUserPasswordCommandTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private function getTester(): CommandTester
    {
        $application = new Application(self::bootKernel());

        return new CommandTester($application->find(SetUserPasswordCommand::CMD));
    }

    public function testExecute(): void
    {
        $tester = $this->getTester();
        $password = 'a-new-long-password';
        $tester->setInputs([$password, $password]);
        self::assertSame(Command::SUCCESS, $tester->execute(['email' => TestReference::USER_17_EMAIL]));

        /** @var User $user */
        $user = $this->getUserRepository()->findOneBy(['email' => TestReference::USER_17_EMAIL]);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($user, $password));
    }

    /**
     * The production fixtures give the main admin a random value which is not a
     * hash: no password matches it.
     */
    public function testFixtureValueIsNotAUsablePassword(): void
    {
        self::bootKernel();
        /** @var User $user */
        $user = $this->getUserRepository()->findOneBy(['email' => TestReference::USER_17_EMAIL]);
        $value = bin2hex(random_bytes(32));
        $user->setPassword($value);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        foreach (['', $value, 'password'] as $candidate) {
            self::assertFalse($hasher->isPasswordValid($user, $candidate));
        }
    }

    public function testErrors(): void
    {
        $tester = $this->getTester();
        $hash = $this->getPasswordHash();
        self::assertSame(Command::FAILURE, $tester->execute(['email' => 'unknown@example.com']));

        foreach ([['too-short'], [str_repeat('a', 65)], ['a-new-long-password', 'another-long-password']] as $inputs) {
            $tester->setInputs($inputs);
            self::assertSame(Command::FAILURE, $tester->execute(['email' => TestReference::USER_17_EMAIL]));
            self::assertSame($hash, $this->getPasswordHash());
        }
    }

    public function testEmailIsNormalized(): void
    {
        $tester = $this->getTester();
        $tester->setInputs(['a-new-long-password', 'a-new-long-password']);
        self::assertSame(Command::SUCCESS, $tester->execute(['email' => ' '.strtoupper(TestReference::USER_17_EMAIL).' ']));
    }

    private function getPasswordHash(): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        /** @var User $user */
        $user = $this->getUserRepository()->findOneBy(['email' => TestReference::USER_17_EMAIL]);

        return $user->getPassword();
    }
}
