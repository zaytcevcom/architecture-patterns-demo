<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Published;

use function App\Components\env;

final readonly class PostPublishedQueue
{
    public static function getQueueName(): string
    {
        return ((env('APP_ENV') !== 'production') ? 'dev-' : '') . 'post-event-post-published';
    }
}
