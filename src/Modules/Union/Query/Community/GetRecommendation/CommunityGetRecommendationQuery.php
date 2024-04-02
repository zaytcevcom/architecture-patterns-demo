<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetRecommendation;

final readonly class CommunityGetRecommendationQuery
{
    public function __construct(
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
        public array|string $fields = [],
    ) {}
}
