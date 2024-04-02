<?php

declare(strict_types=1);

namespace App\Modules\Identity\Event\User;

use function App\Components\env;

final class UserHelper
{
    public function getQueueName(UserQueue $queue): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . $queue->value;
    }
}
