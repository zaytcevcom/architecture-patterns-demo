<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\AudioPlaylist;

use ZayMedia\Shared\Components\Queue\Queue;

final readonly class AudioPlaylistEventPublisher
{
    public function __construct(
        private AudioPlaylistHelper $audioPlaylistHelper,
        private Queue $queue,
    ) {}

    public function handle(AudioPlaylistQueue $event, int $id): void
    {
        $this->queue->publish(
            queue: $this->audioPlaylistHelper->getQueueName($event),
            message: ['id' => $id],
            ttl: 60
        );
    }
}
