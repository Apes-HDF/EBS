<?php

declare(strict_types=1);

namespace App\Mailer\Email\Admin\Group;

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

/**
 * Email that is send when inviting someone to join a group. In this case the account
 * (and its email) is already in the database. So we can directly invite the user
 * to access the group page to accept the invitation.
 */
final class GroupInvitationEmail implements EmailInterface
{
    use EmailTrait;
    use i18nTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire('%brand%')]
        private readonly string $brand
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getEmail(array $context): TemplatedEmail
    {
        /** @var User $user */
        $user = $context['user'];
        /** @var Group $group */
        $group = $context['group'];

        return (new TemplatedEmail())
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans($this->getI18nPrefix().'.subject', [
                    '%brand%' => $this->brand,
                    '%group%' => $group->getName(),
            ], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/admin/group/group_invitation.html.twig')
            ->context($context)
        ;
    }
}
