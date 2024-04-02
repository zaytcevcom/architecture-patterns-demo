<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\Audio;

enum AudioQueue: string
{
    case CREATED = 'audio-created';
    case UPDATED = 'audio-updated';
}
