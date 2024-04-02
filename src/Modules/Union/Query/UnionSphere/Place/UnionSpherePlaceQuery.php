<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\Place;

final readonly class UnionSpherePlaceQuery
{
    public function __construct(
        public ?string $search,
        public int $sort = 1,
        public int $count = 100,
        public int $offset = 0,
        public string $locale = 'en',
    ) {}
}
