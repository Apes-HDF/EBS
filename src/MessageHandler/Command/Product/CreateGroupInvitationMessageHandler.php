<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Product;

use App\Doctrine\Manager\UserManager;
use App\Helper\StringHelper;
use App\Mailer\AppMailer;
use App\Mailer\Email\Admin\Group\GroupInvitationEmail;
use App\Mailer\Email\Security\CreateAccountStep1Email;
use App\Message\Command\Group\CreateGroupInvitationMessage;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
/**
 * @see GroupCrudController::invite
 */
final class CreateGroupInvitationMessageHandler
{
    use SmsNotifierTrait;

    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
        private readonly GroupRepository $groupRepository,
        private readonly AppMailer $appMailer,
        private readonly StringHelper $stringHelper,
        private readonly TranslatorInterface $translator,
        private readonly SmsNotifier $notifier,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    /**
     * 2 cases: the user email can already exists or not. If not, we create an
     * account as whould do the 1st step.
     */
    public function __invoke(CreateGroupInvitationMessage $message): void
    {
        $group = $this->groupRepository->get($message->groupId);
        Assert::notEmpty($message->email);
        Assert::email($message->email);
        $email = $this->stringHelper->normalizeEmail($message->email);
        $user = $this->userRepository->findOneByEmail($email);
        $isNewUser = false;

        // user not found, so we must create a new account (like step1 on the standard workflow)
        if ($user === null) {
            $user = $this->userManager->getStep1User($message->email);
            $this->userManager->save($user, true);
            $isNewUser = true;
        }

        // now create the invitation to the group.
        // check that the user hasn't already have the invitation or doesn't have another role
        if (!$user->hasLink($group)) {
            $this->userManager->addInvitation($user, $group);
            $this->userManager->save($user, true);
        }

        // We just ignore if something is already found. It's not a critial error.
        // For ewample an admin has sent twice the invitation to the same user because
        // he has forgot he has already done it.

        // the notification email is not the same as a new user must confirm its email
        $email = $isNewUser ? CreateAccountStep1Email::class : GroupInvitationEmail::class;
        $this->appMailer->send($email, compact('user', 'group'));
        if (!$isNewUser) {
            $this->sendSms($user, GroupInvitationEmail::class, [
                '%group%' => $group->getName(),
            ]);
        }
    }
}
