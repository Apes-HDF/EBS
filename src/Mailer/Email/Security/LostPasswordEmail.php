<?php

declare(strict_types=1);

namespace App\Mailer\Email\Security;

use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\EmailInterface;
use App\Mailer\Email\EmailTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Email that is send when a user ask to reinitialize its password.
 */
final class LostPasswordEmail implements EmailInterface
{
    use EmailTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getEmail(array $context): TemplatedEmail
    {
        /** @var User $user */
        $user = $context['user'];
        $token = $user->getLostPasswordToken();
        Assert::stringNotEmpty($token, 'Cannot sent the email for a user without lost password token');

        return (new TemplatedEmail())
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans('lost_password.email.subject', [], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/security/lost_password.html.twig')
            ->context($context);
    }
}
