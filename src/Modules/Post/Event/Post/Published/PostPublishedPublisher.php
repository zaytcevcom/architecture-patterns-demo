<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Published;

use ZayMedia\Shared\Components\Queue\Queue;

final readonly class PostPublishedPublisher
{
    public function __construct(
        private Queue $queue,
    ) {}

    public function handle(PostPublishedData $command): void
    {
        $this->queue->publish(
            queue: PostPublishedQueue::getQueueName(),
            message: [
                'userId'    => $command->userId,
                'postId'    => $command->postId,
                'socialIds' => $command->socialIds,
            ],
        );
    }
}
