<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Community;

use App\Modules\Union\Helpers\UnionCacheHelper;
use ZayMedia\Shared\Components\Cacher\Cacher;
use ZayMedia\Shared\Components\Queue\Queue;

final readonly class CommunityEventPublisher
{
    public function __construct(
        private CommunityHelper $communityHelper,
        private Queue $queue,
        private Cacher $cacher,
        private UnionCacheHelper $helper,
    ) {}

    public function handle(CommunityQueue $event, int $id): void
    {
        if ($event === CommunityQueue::UPDATED) {
            // todo: ВЫНЕСТИ В CONSUMER
            $this->cacher->delete(
                key: $this->helper->getKey($id)
            );
        }

        $this->queue->publish(
            queue: $this->communityHelper->getQueueName($event),
            message: ['id' => $id],
            ttl: 60
        );
    }
}
