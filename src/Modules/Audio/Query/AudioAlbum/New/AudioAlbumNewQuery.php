<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\New;

final readonly class AudioAlbumNewQuery
{
    public function __construct(
        public ?string $filter = null,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
