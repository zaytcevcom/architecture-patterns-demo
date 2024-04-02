<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\AudioAlbum;

class AudioAlbumQuery
{
    public function __construct(
        public string $search,
        public ?int $unionId = null,
        public ?bool $isAlbum = null,
        public int $limit = 150
    ) {}
}
