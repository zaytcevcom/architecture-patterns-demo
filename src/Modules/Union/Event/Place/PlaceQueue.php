<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Place;

enum PlaceQueue: string
{
    case CREATED = 'place-created';
    case UPDATED = 'place-updated';
}
