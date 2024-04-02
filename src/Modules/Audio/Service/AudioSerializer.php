<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

class AudioSerializer
{
    public function serialize(?array $audio): ?array
    {
        if (empty($audio)) {
            return null;
        }

        /**
         * @var array{
         *     id:int,
         *     album_id:int,
         *     name:string,
         *     version:string,
         *     explicit:int,
         *     lyrics_file_id:string|null,
         *     artist:string,
         *     duration:int,
         *     text:string,
         *     source:string,
         *     url_hls:string,
         *     count_add:int,
         * } $audio
         */
        return [
            'id'            => $audio['id'],
            'albumId'       => $audio['album_id'],
            'name'          => $audio['name'] ?? '',
            'version'       => (!empty($audio['version'])) ? trim($audio['version']) : null,
            'isExplicit'    => (bool)($audio['explicit'] ?? 0),
            'hasLyrics'     => (bool)($audio['lyrics_file_id'] ?? false),
            'artists'       => isset($audio['artist']) ? array_map('trim', explode(',', $audio['artist'])) : [],
            'duration'      => $audio['duration'] ?? 0,
            'text'          => $audio['text'] ?? null,
            'source'        => [
                'mp3' => $audio['source'] ?? null,
                'hls' => $audio['url_hls'] ?? null,
            ],
            'countAdded'    => $audio['count_add'],
            'isAdded'       => null,
            'likes' => [
                'count'     => $audio['count_add'] ?? 0,
                'canView'   => null,
                'canLike'   => null,
                'isLiked'   => null,
            ],
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
}
