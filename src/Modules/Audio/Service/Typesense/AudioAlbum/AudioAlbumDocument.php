<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\AudioAlbum;

readonly class AudioAlbumDocument
{
    public function __construct(
        public int $identifier,
        public array $unionIds,
        public string $title,
        public array $artists,
        public int $year,
        public bool $isAlbum,
    ) {}
}
