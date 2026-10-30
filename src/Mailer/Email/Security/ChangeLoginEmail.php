<?php

declare(strict_types=1);

namespace App\Mailer\Email\Security;

use App\Controller\i18nTrait;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\EmailInterface;
use App\Mailer\Email\EmailTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Email sent to the new address when a user asks to change its email, to confirm
 * that he owns this address.
 */
final class ChangeLoginEmail implements EmailInterface
{
    use EmailTrait;
    use i18nTrait;

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
        $pendingEmail = $user->getPendingEmail();
        Assert::stringNotEmpty($pendingEmail, 'Cannot sent the email for a user without pending email.');
        Assert::stringNotEmpty($user->getPendingEmailToken(), 'Cannot sent the email for a user without pending email token.');

        return (new TemplatedEmail())
            ->to($pendingEmail)
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans($this->getI18nPrefix().'.subject', [], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/security/change_login.html.twig')
            ->context($context);
    }
}
