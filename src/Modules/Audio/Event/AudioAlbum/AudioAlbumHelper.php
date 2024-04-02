<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\AudioAlbum;

use function App\Components\env;

final class AudioAlbumHelper
{
    public function getQueueName(AudioAlbumQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
