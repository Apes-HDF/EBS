<?php

declare(strict_types=1);

namespace App\Form\Type\ServiceRequest;

class UserLoansProductSelectFormType extends AbstractUserProductSelectFormType
{
    public function isOwner(): bool
    {
        return false;
    }
}
