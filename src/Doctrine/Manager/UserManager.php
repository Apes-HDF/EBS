<?php

declare(strict_types=1);

namespace App\Doctrine\Manager;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\Group\UserMembership;
use App\Helper\FileUploader;
use App\Helper\StringHelper;
use App\Repository\UserRepository;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use Webmozart\Assert\Assert;

final class UserManager
{
    public const PASWWORD_MIN_LENGTH = 8;
    public const PASWWORD_MAX_LENGTH = 64;

    private const CONFIRMATION_TOKEN_LENGTH = 50;
    private const CONFIRMATION_TOKEN_EXPIRATION_TIME = '+24 hours';

    private const LOST_PASSWORD_TOKEN_LENGTH = 50;
    private const LOST_PASSWORD_TOKEN_EXPIRATION_TIME = '+1 hour';

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
        private readonly ClockInterface $clock,
        private readonly StringHelper $stringHelper,
        private readonly FilesystemOperator $userStorage,
        private readonly FileUploader $fileUploader,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Shortcut.
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->userRepository->save($entity, $flush);
    }

    /**
     * Shortcut.
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->userRepository->remove($entity, $flush);
    }

    public function updatePassword(User $user): void
    {
        Assert::stringNotEmpty((string) $user->getPlainPassword(), 'The plainPassword property should be set and not empty.');
        $password = $this->userPasswordHasher->hashPassword($user, (string) $user->getPlainPassword());
        $user->setPassword($password);
    }

    public function updateLoginAt(User $user): void
    {
        $user->setLoginAt($this->clock->now());
        $this->save($user, true);
    }

    /**
     * Normalize email in a reliable way.
     */
    public function normalizeEmail(User $user, string $email): void
    {
        $user->setEmail($this->stringHelper->normalizeEmail($email));
    }

    /**
     * Generate a random token that will be used for the user to confirm its email.
     */
    public function generateConfirmationToken(User $user): void
    {
        $confirmationToken = ByteString::fromRandom(self::CONFIRMATION_TOKEN_LENGTH);
        $user->setConfirmationToken($confirmationToken->toString());
    }

    /**
     * Generate a random token that will be used for the user to confirm its email.
     */
    public function generateLostPasswordToken(User $user): void
    {
        $token = ByteString::fromRandom(self::LOST_PASSWORD_TOKEN_LENGTH);
        $user->setLostPasswordToken($token->toString());
    }

    /**
     * Set the expiration date of the confirmation token.
     */
    public function setConfirmationTokenExpirationDate(User $user): void
    {
        $expiresAt = $this->clock->now()->modify(self::CONFIRMATION_TOKEN_EXPIRATION_TIME);
        $user->setConfirmationExpiresAt($expiresAt);
    }

    /**
     * Set the expiration date of the lost password token.
     */
    public function setLostPasswordExpirationDate(User $user): void
    {
        $expiresAt = $this->clock->now()->modify(self::LOST_PASSWORD_TOKEN_EXPIRATION_TIME);
        $user->setLostPasswordExpiresAt($expiresAt);
    }

    public function refreshConfirmationToken(User $user): void
    {
        $this->generateConfirmationToken($user);
        $this->setConfirmationTokenExpirationDate($user);
    }

    public function getStep1User(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $this->refreshConfirmationToken($user);

        return $user;
    }

    public function refreshLostPasswordToken(User $user): void
    {
        $this->generateLostPasswordToken($user);
        $this->setLostPasswordExpirationDate($user);
    }

    /**
     * Finalization process and cleanup for step 2 of the account creation.
     */
    public function finalizeAccountCreateStep2(User $user): void
    {
        $user->confirmEmail();
        $user->resetConfirmation();
    }

    /**
     * Set the user's new email.
     */
    public function changeLogin(User $user, string $email): void
    {
        $user->setEmail($email);
    }

    /**
     * Add a membership for a free group.
     */
    public function addToGroup(User $user, Group $group, UserMembership $userMembership = UserMembership::MEMBER): void
    {
        $userGroup = (new UserGroup())
            ->setUser($user)
            ->setGroup($group)
            ->setMembership($userMembership);
        $user->addUserGroup($userGroup);
    }

    public function addInvitation(User $user, Group $group): void
    {
        $this->addToGroup($user, $group, UserMembership::INVITATION);
    }

    public function upload(?UploadedFile $image, User $user): void
    {
        if ($image !== null) {
            $imageUploaded = $this->fileUploader->uploadImage($this->userStorage, $image);
            $user->setAvatar($imageUploaded);
        }
    }

    public function deleteAvatar(User $user): User
    {
        try {
            $this->userStorage->delete((string) $user->getAvatar());
        } catch (FilesystemException $e) {
            $this->logger->warning(\sprintf('Unable to avatar of user (%s) image %s: %s', $user->getId(), $user->getAvatar(), $e->getMessage()));
        }
        $user->deleteAvatar();
        $this->save($user, true);

        return $user;
    }

    /**
     * Add the email normalization step when submitting a form implying a user so
     * the unique constraint on the email can work properly.
     */
    public function addEmailNormalizeSubmitEvent(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();
            $this->normalizeEmail($user, $user->getEmail());
            $event->setData($user);
        });
    }
}
