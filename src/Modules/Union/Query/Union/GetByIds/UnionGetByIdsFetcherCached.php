<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByIds;

use App\Components\Cacher\CacheFetcher;
use App\Modules\Union\Helpers\UnionCacheHelper;
use ZayMedia\Shared\Components\Cacher\Cacher;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayInt;

final class UnionGetByIdsFetcherCached
{
    private ?CacheFetcher $cacheFetcher = null;

    public function __construct(
        private readonly Cacher $cacher,
        private readonly UnionGetByIdsFetcher $fetcher,
        private readonly UnionCacheHelper $cacheHelper,
    ) {}

    public function fetch(UnionGetByIdsQuery $query): array
    {
        $ids = toArrayInt($query->ids);

        $result = $this->cacheFetcher()->fetch($ids);
        $items = $result->items;

        if (\count($result->notExistsIds) !== 0) {
            $itemsDB = $this->getFromDB($result->notExistsIds);
            $this->cacheFetcher()->save($itemsDB);

            $items = array_merge($items, $itemsDB);
        }

        /** @var array{array} $items */
        return Helper::sortItemsByIds($items, $ids);
    }

    /** @return array<string, mixed>[] */
    private function getFromDB(array $ids): array
    {
        /** @var array<string, mixed>[] */
        return $this->fetcher->fetch(
            new UnionGetByIdsQuery(
                ids: $ids
            )
        );
    }

    private function cacheFetcher(): CacheFetcher
    {
        if (null === $this->cacheFetcher) {
            $this->cacheFetcher = new CacheFetcher(
                $this->cacher,
                $this->cacheHelper
            );
        }

        return $this->cacheFetcher;
    }
}
