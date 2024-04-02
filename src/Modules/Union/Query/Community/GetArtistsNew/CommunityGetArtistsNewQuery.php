<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetArtistsNew;

final readonly class CommunityGetArtistsNewQuery
{
    public function __construct(
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
