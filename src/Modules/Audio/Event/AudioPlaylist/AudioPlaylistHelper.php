<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\AudioPlaylist;

use function App\Components\env;

final class AudioPlaylistHelper
{
    public function getQueueName(AudioPlaylistQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
