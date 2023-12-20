<?php

declare(strict_types=1);

namespace App\Enum\Product;

use App\Enum\AsArrayTrait;

enum ProductAvailabilityType: string
{
    use AsArrayTrait;

    case OWNER = 'owner'; // a period choosen by the owner of the product
    case SERVICE_REQUEST = 'service_request'; // a period related to a service request (and product)
}
