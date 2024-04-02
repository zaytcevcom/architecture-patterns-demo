<?php

declare(strict_types=1);

namespace App\Components\Cacher;

use ZayMedia\Shared\Components\Cacher\Cacher;

readonly class CacheFetcher
{
    public function __construct(
        private Cacher $cacher,
        private CacheHelper $helper,
        private ?string $prefixKey = null
    ) {}

    /** @param int[]|string[] $ids */
    public function fetch(array $ids, string $field = 'id'): CacheFetcherResult
    {
        /** @var array{false|string} $result */
        $result = $this->cacher->mGet(
            $this->helper->getKeys($ids, $this->prefixKey)
        );

        $items = [];

        foreach ($result as $res) {
            if ($res !== false) {
                $items[] = $res;
            }
        }

        /** @var array<string, mixed>[] $items */
        $items = array_map(
            static fn (string $v): array => (array)json_decode($v, true),
            $items
        );

        return new CacheFetcherResult(
            items: $items,
            notExistsIds: $this->getNotExistsIds($ids, $items, $field)
        );
    }

    /** @param array<string, mixed>[] $items */
    public function save(array $items, string $field = 'id'): void
    {
        foreach ($items as $item) {
            if (!isset($item[$field])) {
                continue;
            }

            $value = $item[$field];

            if (!\is_string($value) && !\is_int($value)) {
                continue;
            }

            $this->cacher->set(
                key: $this->helper->getKey($value, $this->prefixKey),
                value: json_encode($item),
                ttl: $this->helper->getTTL()
            );
        }
    }

    /**
     * @param array<string, mixed>[] $items
     * @return array<string, mixed>[]
     */
    private function getNotExistsIds(array $ids, array $items, string $field = 'id'): array
    {
        /** @var array<string, mixed>[] */
        return array_diff(
            $ids,
            array_map(
                static fn (array $item): int|string => is_numeric($item[$field]) ? (int)$item[$field] : (string)$item[$field],
                $items
            )
        );
    }
}
