<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Event;

use App\Modules\Union\Helpers\UnionCacheHelper;
use ZayMedia\Shared\Components\Cacher\Cacher;
use ZayMedia\Shared\Components\Queue\Queue;

final readonly class EventEventPublisher
{
    public function __construct(
        private EventHelper $eventHelper,
        private Queue $queue,
        private Cacher $cacher,
        private UnionCacheHelper $helper,
    ) {}

    public function handle(EventQueue $event, int $id): void
    {
        if ($event === EventQueue::UPDATED) {
            // todo: ВЫНЕСТИ В CONSUMER
            $this->cacher->delete(
                key: $this->helper->getKey($id)
            );
        }

        $this->queue->publish(
            queue: $this->eventHelper->getQueueName($event),
            message: ['id' => $id],
            ttl: 60
        );
    }
}
