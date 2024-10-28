<?php

declare(strict_types=1);

namespace App\Mailer\Email\Security;

use App\Controller\i18nTrait;
use App\Entity\Group;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\EmailInterface;
use App\Mailer\Email\EmailTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Email that is send after the step1 of the account creation process or for an
 * invitation to a group.
 */
final class CreateAccountStep1Email implements EmailInterface
{
    use EmailTrait;
    use i18nTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getEmail(array $context): TemplatedEmail
    {
        /** @var User $user */
        $user = $context['user'];
        Assert::stringNotEmpty($user->getConfirmationToken(), 'Cannot sent the confirmation email for a user without confirmationToken.');

        // Is it an email for an invitation? Yes, if the context has a group object associated.
        /** @var ?Group $group */
        $group = $context['group'] ?? null;
        $subjectKey = $this->getI18nPrefix().'.subject';
        if (isset($context['group'])) {
            $subjectKey .= '.invitation';
        }

        return (new TemplatedEmail())
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans($subjectKey, [
                '%brand%' => $this->brand,
                '%group%' => $group?->getName(),
            ], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/security/create_account_step1.html.twig')
            ->context($context)
        ;
    }
}
