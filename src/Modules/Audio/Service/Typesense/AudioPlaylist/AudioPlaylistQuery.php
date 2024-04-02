<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\AudioPlaylist;

class AudioPlaylistQuery
{
    public function __construct(
        public string $search,
        public ?int $unionId = null,
        public int $limit = 150
    ) {}
}
