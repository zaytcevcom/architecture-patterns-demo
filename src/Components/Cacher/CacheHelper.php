<?php

declare(strict_types=1);

namespace App\Components\Cacher;

interface CacheHelper
{
    public function getKey(int|string $id, ?string $postfix = null): string;

    /** @param int[]|string[] $ids */
    public function getKeys(array $ids, ?string $postfix = null): array;

    public function getTTL(): int;
}
