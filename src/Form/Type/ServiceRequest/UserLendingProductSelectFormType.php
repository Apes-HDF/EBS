<?php

declare(strict_types=1);

namespace App\Form\Type\ServiceRequest;

final class UserLendingProductSelectFormType extends AbstractUserProductSelectFormType
{
    public function isOwner(): bool
    {
        return true;
    }
}
