<?php

declare(strict_types=1);

namespace App\Enum\ServiceRequest;

use App\Enum\AsArrayTrait;

/**
 * @see config/packages/workflow.yaml
 * @see docs/loan_status_workflow.png
 */
enum ServiceRequestStatus: string
{
    use AsArrayTrait;

    /**
     * Service has been created by the borrower and has to be confirmed by the lender.
     * (owner of the product or service).
     */
    case NEW = 'new';

    /**
     * Service has been accepted by the lender and has to be confirmed again by the
     * borrower to continue the transaction.
     */
    case TO_CONFIRM = 'to_confirm';

    /**
     * Loan has been confirmed by both lender and borrower. The transaction can
     * now take place at the planned date (startAt).
     */
    case CONFIRMED = 'confirmed';

    /**
     * The request service has been canceled by the lender or the borrower and cannot
     * be finalized. The request service can be refused at various steps.
     */
    case REFUSED = 'refused';

    /**
     * The end date of the request service is now over or the lender has confirmed
     * before the end date that the object has been returned (loadn). A service
     * is finished ar soon as the end date is due.
     */
    case FINISHED = 'finished';

    public function isNew(): bool
    {
        return $this === self::NEW;
    }

    public function isToConfirm(): bool
    {
        return $this === self::TO_CONFIRM;
    }

    public function isRefused(): bool
    {
        return $this === self::REFUSED;
    }

    public function isConfirmed(): bool
    {
        return $this === self::CONFIRMED;
    }

    public function isFinished(): bool
    {
        return $this === self::FINISHED;
    }

    public function isOngoing(): bool
    {
        return $this->isNew() || $this->isToConfirm() || $this->isConfirmed();
    }
}
