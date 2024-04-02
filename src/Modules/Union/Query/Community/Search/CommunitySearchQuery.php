<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\Search;

final readonly class CommunitySearchQuery
{
    public function __construct(
        public ?int $sphereId = null,
        public ?int $categoryId = null,
        public ?int $categoryKind = null,
        public string $search = '',
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
        public array|string $fields = [],
    ) {}
}
