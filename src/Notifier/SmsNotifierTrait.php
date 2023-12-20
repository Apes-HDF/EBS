<?php

declare(strict_types=1);

namespace App\Notifier;

use App\Controller\i18nTrait;
use App\Entity\User;
use App\Mailer\AppMailer;

trait SmsNotifierTrait
{
    use i18nTrait;

    /**
     * Send a SMS with the subject of a given email.
     *
     * @param class-string         $emailClass
     * @param array<string, mixed> $subjectContext addtional context for the subject
     */
    private function sendSms(User $user, string $emailClass, array $subjectContext = []): void
    {
        $i18nPrefix = $this->getI18nPrefix($emailClass);
        $subject = $this->translator->trans($i18nPrefix.'.subject', array_merge(['%brand%' => $this->brand], $subjectContext), AppMailer::TR_DOMAIN);
        $this->notifier->notify(
            $user,
            $subject,
        );
    }
}
