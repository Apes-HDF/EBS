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
        $page = $request->query->getInt($key, 1);
        $page = max($page, 1); // no negative page or 0

        // limit max page to 100000 (2 million products)

        return min($page, 100000);
    }
}
