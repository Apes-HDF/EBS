<?php

declare(strict_types=1);

namespace App\Search\Subscriber;

use Knp\Component\Pager\Event\ItemsEvent;
use Meilisearch\Search\SearchResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allow to paginate a Meilisearch SearhResult.
 */
final class SearchResultSubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event): void
    {
        $searchResult = $event->target;
        if (!$searchResult instanceof SearchResult) {
            return;
        }

        $event->count = (int) $searchResult->getTotalHits();
        $event->items = $searchResult->getHits();
        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', 1/* increased priority to override any internal */],
        ];
    }
}
