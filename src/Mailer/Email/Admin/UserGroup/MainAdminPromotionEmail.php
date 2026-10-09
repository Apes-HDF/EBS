<?php

declare(strict_types=1);

namespace App\Mailer\Email\Admin\UserGroup;

use App\Controller\i18nTrait;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\EmailInterface;
use App\Mailer\Email\EmailTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Email sent when an admin is promoted to the main admin of the group.
 */
final readonly class MainAdminPromotionEmail implements EmailInterface
{
    use EmailTrait;
    use i18nTrait;

    public function __construct(
        private TranslatorInterface $translator,
        #[Autowire(param: 'brand')]
        private string $brand,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getEmail(array $context): TemplatedEmail
    {
        /** @var User $user */
        $user = $context['user'];

        return (new TemplatedEmail())
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans($this->getI18nPrefix().'.subject', ['%brand%' => $this->brand], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/admin/user_group/main_admin_promotion.html.twig')
            ->context($context)
        ;
    }
}
