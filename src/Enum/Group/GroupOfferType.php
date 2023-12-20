<?php

declare(strict_types=1);

namespace App\Enum\Group;

use App\Enum\AsArrayTrait;

enum GroupOfferType: string
{
    use AsArrayTrait;

    // The user only to pay once to access the group. In his case the end date is
    // not set and the membership is valid until it is deleted or a end date is
    // set. The end date can always be set manually in case of a problem.
    case ONESHOT = 'oneshot';

    // Monthly subscription. The membership is valid 1 month and the user has to
    // renew it once the end date is over. This can be useful when a user when to
    // try a group on the short period before taking a longer subscription.
    case MONTHLY = 'monthly';

    // Subscription valid for one year. An email will be send a few days before
    // the end of the membership
    case YEARLY = 'yearly';

    public function isMonthly(): bool
    {
        return $this === self::MONTHLY;
    }

    public function isYearly(): bool
    {
        return $this === self::YEARLY;
    }

    public function isRecurring(): bool
    {
        return $this->isYearly() || $this->isMonthly();
    }

    public function getEndAtInterval(): string
    {
        return match ($this) {
            self::YEARLY => '+1 year midnight',
            self::MONTHLY => '+1 month midnight',
            self::ONESHOT => '',
        };
    }
}
