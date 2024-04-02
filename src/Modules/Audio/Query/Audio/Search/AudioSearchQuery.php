<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\Search;

final readonly class AudioSearchQuery
{
    public function __construct(
        public string $search = '',
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
