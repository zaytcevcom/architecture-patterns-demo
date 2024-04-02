<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\Audio;

use function App\Components\env;

final class AudioHelper
{
    public function getQueueName(AudioQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
