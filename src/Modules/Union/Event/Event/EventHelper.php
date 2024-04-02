<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Event;

use function App\Components\env;

final class EventHelper
{
    public function getQueueName(EventQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
