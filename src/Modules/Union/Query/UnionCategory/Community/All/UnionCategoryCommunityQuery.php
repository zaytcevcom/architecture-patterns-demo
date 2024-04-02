<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionCategory\Community\All;

final readonly class UnionCategoryCommunityQuery
{
    public function __construct(
        public ?string $search,
        public ?string $filter,
        public int $sort = 1,
        public int $count = 100,
        public int $offset = 0,
        public string $locale = 'en',
    ) {}
}
