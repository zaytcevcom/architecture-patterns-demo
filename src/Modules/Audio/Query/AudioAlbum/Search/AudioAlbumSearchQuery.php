<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\Search;

final readonly class AudioAlbumSearchQuery
{
    public function __construct(
        public string $search = '',
        public ?string $filter = null,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
