<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetByIds;

use App\Components\Cacher\CacheFetcher;
use App\Modules\Identity\Helpers\IdentityCacheHelper;
use ZayMedia\Shared\Components\Cacher\Cacher;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayInt;

final class IdentityGetByIdsFetcherCached
{
    private ?CacheFetcher $cacheFetcher = null;

    public function __construct(
        private readonly Cacher $cacher,
        private readonly IdentityGetByIdsFetcher $fetcher,
        private readonly IdentityCacheHelper $cacheHelper,
    ) {}

    public function fetch(IdentityGetByIdsQuery $query): array
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
            new IdentityGetByIdsQuery(
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
