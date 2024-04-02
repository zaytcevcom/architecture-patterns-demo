<?php

declare(strict_types=1);

namespace App\Modules;

final readonly class ResultCountItems
{
    public function __construct(
        public int $count,
        public array $items
    ) {}
}
