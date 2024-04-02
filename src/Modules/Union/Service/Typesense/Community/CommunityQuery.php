<?php

declare(strict_types=1);

namespace App\Modules\Union\Service\Typesense\Community;

readonly class CommunityQuery
{
    public function __construct(
        public string $search,
        public ?int $sphereId = null,
        public ?int $categoryId = null,
        public ?int $categoryKind = null,
        public int $limit = 150
    ) {}
}
