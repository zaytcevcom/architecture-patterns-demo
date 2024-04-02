<?php

declare(strict_types=1);

namespace App\Modules\Identity\Event\User;

enum UserQueue: string
{
    case CREATED = 'user-created';
    case UPDATED = 'user-updated';
    case OPENED = 'user-opened';
}
