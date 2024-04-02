<?php

declare(strict_types=1);

namespace App\Modules\Audio\Event\AudioPlaylist;

enum AudioPlaylistQueue: string
{
    case CREATED = 'audio-playlist-created';
    case UPDATED = 'audio-playlist-updated';
}
