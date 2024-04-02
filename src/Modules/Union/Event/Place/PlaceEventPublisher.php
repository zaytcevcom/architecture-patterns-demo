<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Place;

use App\Modules\Union\Helpers\UnionCacheHelper;
use ZayMedia\Shared\Components\Cacher\Cacher;
use ZayMedia\Shared\Components\Queue\Queue;

final readonly class PlaceEventPublisher
{
    public function __construct(
        private PlaceHelper $placeHelper,
        private Queue $queue,
        private Cacher $cacher,
        private UnionCacheHelper $helper,
    ) {}

    public function handle(PlaceQueue $event, int $id): void
    {
        if ($event === PlaceQueue::UPDATED) {
            // todo: ВЫНЕСТИ В CONSUMER
            $this->cacher->delete(
                key: $this->helper->getKey($id)
            );
        }

        $this->queue->publish(
            queue: $this->placeHelper->getQueueName($event),
            message: ['id' => $id],
            ttl: 60
        );
    }
}
