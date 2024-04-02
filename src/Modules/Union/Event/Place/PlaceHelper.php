<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Place;

use function App\Components\env;

final class PlaceHelper
{
    public function getQueueName(PlaceQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
