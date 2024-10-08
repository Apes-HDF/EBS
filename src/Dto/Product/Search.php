<?php

declare(strict_types=1);

namespace App\Dto\Product;

use App\Entity\Address;
use App\Entity\Category;
use App\Entity\User;
use App\Form\Type\Product\SearchFormType;

/**
 * @see SearchFormType
 */
final class Search
{
    public function __construct(string $q, int $page = 1, ?User $user = null)
    {
        $this->q = $q;
        $this->page = $page;
        $this->user = $user;
    }

    /**
     * Search query. Eg: "vélo".
     */
    public string $q = '';

    /**
     * Requested page for paginated results.
     */
    public int $page = 1;

    /**
     * Category filter.
     */
    public ?Category $category = null;

    /**
     * Place filter.
     */
    public ?User $place = null;

    /**
     * Current logged user.
     */
    public ?User $user = null;

    /**
     * City filter Eg: "Lille". The distance filter is only applied when we have
     * bother a city and a distance.
     */
    public ?Address $city = null;

    /**
     * Distance filter related to the city.
     */
    public ?int $distance = null;

    public function hasQuery(): bool
    {
        return $this->q !== '';
    }

    public function hasCity(): bool
    {
        return $this->city !== null;
    }

    public function hasDistance(): bool
    {
        return $this->distance !== null;
    }

    /**
     * Test if we have both a city (as an address with a non empty locality) and
     * a distance.
     */
    public function hasProximity(): bool
    {
        return $this->hasCity()
            && ($this->city?->hasLocality() ?? false)
            && $this->hasDistance()
        ;
    }

    /**
     * If the user is not null, then it is logged.
     */
    public function isLogged(): bool
    {
        return $this->user !== null;
    }
}
