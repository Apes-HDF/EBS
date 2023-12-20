<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * Application payment token extending the Payoum base one.
 *
 * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#configure
 */
#[ORM\Entity]
#[ORM\Table]
class PaymentToken extends Token
{
    use TimestampableEntity;
}
