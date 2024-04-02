<?php

declare(strict_types=1);

namespace App\Components\CursorPagination;

final readonly class CursorPaginationResult
{
    public function __construct(
        public ?int $count,
        public array $items,
        public Cursor $cursor,
    ) {}
}
