<?php

declare(strict_types=1);

namespace App\Enum\Payment;

use Mollie\Api\Types\PaymentMethod as MolliePaymentMethod;

/**
 * For now we only use the credit card method. This payment method must be activated
 * on the Mollie dashboard.
 *
 * @see https://help.mollie.com/hc/en-us/articles/115000648269-How-do-I-activate-payment-methods-
 * @see https://github.com/webbaard/payum-mollie
 * @see MolliePaymentMethod::CREDITCARD
 */
enum PaymentMethod: string
{
    case CREDITCARD = 'creditcard';
}
