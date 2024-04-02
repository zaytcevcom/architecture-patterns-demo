<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

class AudioPlaylistSerializer
{
    public function serialize(?array $audioPlaylist): ?array
    {
        if (empty($audioPlaylist)) {
            return null;
        }

        return [
            'id'            => $audioPlaylist['id'],
            'unionId'       => $audioPlaylist['union_id'],
            'photo'         => $this->getPhoto($audioPlaylist),
            'name'          => $audioPlaylist['name'] ?? '',
            'description'   => $audioPlaylist['description'] ?? '',
            'isExplicit'    => (bool)($audioPlaylist['explicit'] ?? 0),
            'duration'      => $audioPlaylist['duration'] ?? 0,
            'updatedAt'     => $audioPlaylist['updated_at'],
            'publishedAt'   => $audioPlaylist['published_at'],
            'counters'  => [
                'audios'    => $audioPlaylist['count_audio'] ?? 0,
                'add'       => $audioPlaylist['count_add'] ?? 0,
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
            /** @var string[] $sizes */
            $sizes = json_decode($item['photo'], true);

            /** @var string[] $cropSizes */
            $cropSizes = $sizes['crop_square'] ?? [];
            ksort($cropSizes);
            $cropSizes = array_values($cropSizes);

            if (\count($cropSizes) > 0) {
                return [
                    'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                    'original'  => $sizes['original'] ?? null,
                ];
            }
        }

        return null;
    }
}
