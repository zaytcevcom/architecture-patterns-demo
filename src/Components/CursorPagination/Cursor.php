<?php

declare(strict_types=1);

namespace App\Components\CursorPagination;

final readonly class Cursor
{
    public function __construct(
        public ?string $prev,
        public ?string $next,
    ) {}
}
