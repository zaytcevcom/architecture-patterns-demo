<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Event;

enum EventQueue: string
{
    case CREATED = 'event-created';
    case UPDATED = 'event-updated';
}
