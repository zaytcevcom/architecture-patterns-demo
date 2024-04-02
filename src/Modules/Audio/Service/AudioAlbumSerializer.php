<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

class AudioAlbumSerializer
{
    public function serialize(?array $audioAlbum): ?array
    {
        if (empty($audioAlbum)) {
            return null;
        }

        /**
         * @var array{
         *     id:int,
         *     genre_ids:string|null,
         *     name:string,
         *     description:string,
         *     version:string,
         *     explicit:int,
         *     duration:int,
         *     year:int,
         *     audio_count:int,
         *     count_add:int,
         *     count_likes:int,
         * } $audioAlbum
         */
        return [
            'id'            => $audioAlbum['id'],
            'photo'         => $this->getPhoto($audioAlbum),
            'photoAnimated' => $this->getPhotoAnimated($audioAlbum),
            'genreIds'      => $this->getGenreIds($audioAlbum),
            'name'          => $audioAlbum['name'] ?? '',
            'description'   => $audioAlbum['description'] ?? '',
            'version'       => (!empty($audioAlbum['version'])) ? trim($audioAlbum['version']) : null,
            'isExplicit'    => (bool)($audioAlbum['explicit'] ?? 0),
            'duration'      => $audioAlbum['duration'] ?? 0,
            'year'          => $audioAlbum['year'] ?? null,
            'counters'  => [
                'audios'    => $audioAlbum['audio_count'] ?? 0,
                'add'       => $audioAlbum['count_add'] ?? 0,
                'likes'     => $audioAlbum['count_likes'] ?? 0, // todo: delete?
            ],
            'likes' => [
                'count'     => $audioAlbum['count_likes'] ?? 0,  // todo: delete?
                'canView'   => null,
            ],
            'isAdded' => null,
            'union'   => null,
            'genres'  => [],
        ];
    }

    public function serializeItems(array $items): array
    {
        $result = [];

        /** @var array $item */
        foreach ($items as $item) {
            $result[] = $this->serialize($item);
        }

        return $result;
    }

    private function getPhoto(array $item): ?array
    {
        /** @var array{photo:string} $item */
        if (!empty($item['photo'])) {
            /** @var bool|string[]|null $sizes */
            $sizes = json_decode($item['photo'], true);

            if (!\is_array($sizes)) {
                return null;
            }

            /** @var bool|string[]|null $cropSizes */
            $cropSizes = $sizes['crop_square'] ?? [];

            if (!\is_array($cropSizes)) {
                return null;
            }

            ksort($cropSizes);
            $cropSizes = array_values($cropSizes);

            if (\count($cropSizes) > 0) {
                return [
                    'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                ];
            }
        }

        return null;
    }

    private function getPhotoAnimated(array $item): ?array
    {
        /** @var array{photo_animated:string} $item */
        if (!empty($item['photo_animated'])) {
            /** @var bool|string[]|null $sizes */
            $sizes = json_decode($item['photo_animated'], true);

            if (!\is_array($sizes)) {
                return null;
            }

            /** @var bool|string[]|null $cropSizes */
            $cropSizes = $sizes['sizes'] ?? [];

            if (!\is_array($cropSizes)) {
                return null;
            }

            ksort($cropSizes);
            $cropSizes = array_values($cropSizes);

            if (\count($cropSizes) > 0) {
                return [
                    'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                ];
            }
        }

        return null;
    }

    private function getGenreIds(array $item): array
    {
        /** @var array{genre_ids:string|null} $item */
        $items = explode(',', $item['genre_ids'] ?? '');

        $result = [];

        foreach ($items as $item) {
            $result[] = (int)$item;
        }

        return array_unique($result);
    }
}
