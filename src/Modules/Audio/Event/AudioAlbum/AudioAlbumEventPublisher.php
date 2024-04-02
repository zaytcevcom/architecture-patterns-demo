<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\AudioAlbum;

use ZayMedia\Shared\Components\Queue\Queue;

final readonly class AudioAlbumEventPublisher
{
    public function __construct(
        private AudioAlbumHelper $audioAlbumHelper,
        private Queue $queue,
    ) {}

    public function handle(AudioAlbumQueue $event, int $id): void
    {
        $this->queue->publish(
            queue: $this->audioAlbumHelper->getQueueName($event),
            message: ['id' => $id],
            ttl: 60
        );
    }
}
