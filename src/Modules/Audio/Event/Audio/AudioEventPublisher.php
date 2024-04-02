<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\Audio;

use ZayMedia\Shared\Components\Queue\Queue;

final readonly class AudioEventPublisher
{
    public function __construct(
        private AudioHelper $audioHelper,
        private Queue $queue,
    ) {}

    public function handle(AudioQueue $event, int $id): void
    {
        $this->queue->publish(
            queue: $this->audioHelper->getQueueName($event),
            message: ['id' => $id],
            ttl: 60
        );
    }
}
