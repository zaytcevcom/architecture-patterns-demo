<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetByHashtag;

final readonly class PostGetByHashtagQuery
{
    public function __construct(
        public string $hashtag = '',
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
        public array|string $fields = [],
    ) {}
}
