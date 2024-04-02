<?php

declare(strict_types=1);

namespace App\Modules\Union\Helpers;

use App\Components\Cacher\CacheHelper;

use function App\Components\env;

class UnionCacheHelper implements CacheHelper
{
    public function getKey(int|string $id, ?string $postfix = null): string
    {
        $postfix = trim($postfix ?? '');
        $postfix = (!empty($postfix)) ? ':' . $postfix : '';
        return $this->getEnv() . 'union:' . $id . $postfix;
    }

    public function getKeys(array $ids, ?string $postfix = null): array
    {
        $result = [];

        foreach ($ids as $id) {
            $result[] = $this->getKey($id, $postfix);
        }

        return $result;
    }

    public function getTTL(): int
    {
        return 7 * 24 * 3600;
    }

    private function getEnv(): string
    {
        return (env('APP_ENV') !== 'production') ? 'dev:' : '';
    }
}
