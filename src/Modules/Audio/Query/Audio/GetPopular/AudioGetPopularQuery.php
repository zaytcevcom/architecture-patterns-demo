<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetPopular;

final readonly class AudioGetPopularQuery
{
    public function __construct(
        public int $count = 100,
        public int $offset = 0,
    ) {}
}
