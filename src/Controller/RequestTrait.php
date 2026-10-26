<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Request helpers.
 */
trait RequestTrait
{
    /**
     * Get a valid page number that is equal or greater than one.
     */
    public function getPage(Request $request, string $key = 'page'): int
    {
        // getInt() throws a BadRequestException on non-numeric values since Symfony 7
        $page = $request->query->filter($key, 1, \FILTER_VALIDATE_INT, ['flags' => \FILTER_NULL_ON_FAILURE]);
        $page = \is_int($page) ? max($page, 1) : 1; // no negative page, 0 or invalid value

        // limit max page to 100000 (2 million products)

        return min($page, 100000);
    }
}
