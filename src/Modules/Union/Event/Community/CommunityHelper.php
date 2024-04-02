<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Community;

use function App\Components\env;

final class CommunityHelper
{
    public function getQueueName(CommunityQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
