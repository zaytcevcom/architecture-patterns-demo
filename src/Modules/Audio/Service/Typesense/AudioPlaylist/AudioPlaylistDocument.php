<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\AudioPlaylist;

readonly class AudioPlaylistDocument
{
    public function __construct(
        public int $identifier,
        public int $unionId,
        public string $title,
        public array $artists,
    ) {}
}
