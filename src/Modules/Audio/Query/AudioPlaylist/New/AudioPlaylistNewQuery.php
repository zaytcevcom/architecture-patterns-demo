<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\New;

final readonly class AudioPlaylistNewQuery
{
    public function __construct(
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
