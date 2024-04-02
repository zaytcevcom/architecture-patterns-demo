<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\AudioAlbum;

enum AudioAlbumQueue: string
{
    case CREATED = 'audio-album-created';
    case UPDATED = 'audio-album-updated';
}
