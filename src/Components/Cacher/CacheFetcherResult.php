<?php

declare(strict_types=1);

namespace App\Components\Cacher;

readonly class CacheFetcherResult
{
    public function __construct(
        /** @var array<string, mixed>[] */
        public array $items,
        public array $notExistsIds,
    ) {}
}
