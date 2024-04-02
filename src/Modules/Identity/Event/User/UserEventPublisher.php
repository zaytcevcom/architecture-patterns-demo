<?php

declare(strict_types=1);

namespace App\Modules\Identity\Event\User;

use App\Modules\Identity\Helpers\IdentityCacheHelper;
use ZayMedia\Shared\Components\Cacher\Cacher;
use ZayMedia\Shared\Components\Queue\Queue;

final readonly class UserEventPublisher
{
    public function __construct(
        private UserHelper $userHelper,
        private Queue $queue,
        private Cacher $cacher,
        private IdentityCacheHelper $helper,
    ) {}

    public function handle(UserQueue $event, int $id): void
    {
        if ($event === UserQueue::UPDATED) {
            // todo: ВЫНЕСТИ В CONSUMER
            $this->cacher->delete(
                key: $this->helper->getKey($id)
            );
        }

        $this->queue->publish(
            queue: $this->userHelper->getQueueName($event),
            message: ['id' => $id],
        );
    }
}
