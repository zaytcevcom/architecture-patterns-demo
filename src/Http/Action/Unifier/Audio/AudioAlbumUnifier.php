<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Audio;

use App\Modules\Audio\Query\AudioAlbumUnion\GetByAlbumIds\AudioAlbumUnionGetByAlbumIdsFetcher;
use App\Modules\Audio\Query\AudioAlbumUnion\GetByAlbumIds\AudioAlbumUnionGetByAlbumIdsQuery;
use App\Modules\Audio\Query\AudioAlbumUser\GetAddedAudioAlbumIds\AudioUserGetAddedAudioAlbumIdsFetcher;
use App\Modules\Audio\Query\AudioAlbumUser\GetAddedAudioAlbumIds\AudioUserGetAddedAudioAlbumIdsQuery;
use App\Modules\Audio\Query\AudioGenre\GetByIds\AudioGenreGetByIdsFetcher;
use App\Modules\Audio\Query\AudioGenre\GetByIds\AudioGenreGetByIdsQuery;
use App\Modules\Audio\Service\AudioAlbumSerializer;
use App\Modules\Audio\Service\AudioGenreSerializer;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsFetcherCached;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsQuery;
use App\Modules\Union\Service\UnionSerializer;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class AudioAlbumUnifier implements UnifierInterface
{
    public function __construct(
        private AudioAlbumSerializer $audioAlbumSerializer,
        private AudioGenreGetByIdsFetcher $audioGenreGetByIdsFetcher,
        private AudioGenreSerializer $audioGenreSerializer,
        private UnionGetByIdsFetcherCached $unionGetByIdsFetcher,
        private UnionSerializer $unionSerializer,
        private AudioUserGetAddedAudioAlbumIdsFetcher $audioUserGetAddedAudioAlbumIdsFetcher,
        private AudioAlbumUnionGetByAlbumIdsFetcher $audioAlbumUnionGetByAlbumIdsFetcher,
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->audioAlbumSerializer->serializeItems($items);

        $entityIds = $this->getEntityIds($items);

        $items = $this->mapGenres($items, $this->getGenres($entityIds['genreIds']));

        $items = $this->mapUnions($items, $this->getAlbumUnions($entityIds['albumIds']));

        if (null !== $userId) {
            $items = $this->mapAdded($items, $this->getAdded($userId, $entityIds['albumIds']));
        }

        return $items;
    }

    private function getGenres(array $ids): array
    {
        return $this->audioGenreSerializer->serializeItems(
            $this->audioGenreGetByIdsFetcher->fetch(
                new AudioGenreGetByIdsQuery($ids)
            )
        );
    }

    private function getUnions(array $ids): array
    {
        return $this->unionSerializer->serializeItems(
            $this->unionGetByIdsFetcher->fetch(
                new UnionGetByIdsQuery($ids)
            )
        );
    }

    private function getAlbumUnions(array $ids): array
    {
        $albumUnions = $this->audioAlbumUnionGetByAlbumIdsFetcher->fetch(
            new AudioAlbumUnionGetByAlbumIdsQuery($ids)
        );

        $unionIds = [];

        foreach ($albumUnions as $albumUnion) {
            $unionIds[] = $albumUnion['union_id'];
        }

        $unions = $this->getUnions($unionIds);

        $result = [];

        foreach ($albumUnions as $albumUnion) {
            /** @var array{id: int} $union */
            foreach ($unions as $union) {
                if ($albumUnion['union_id'] === $union['id']) {
                    if (!isset($result[$albumUnion['album_id']])) {
                        $result[$albumUnion['album_id']] = [];
                    }
                    $result[$albumUnion['album_id']][] = $union;
                    break;
                }
            }
        }

        return $result;
    }

    private function getAdded(int $userId, array $ids): array
    {
        return $this->audioUserGetAddedAudioAlbumIdsFetcher->fetch(
            new AudioUserGetAddedAudioAlbumIdsQuery($userId, $ids)
        );
    }

    private function mapGenres(array $items, array $genres): array
    {
        /** @var array{array{genreIds:int[]}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['genres'] = [];

            foreach ($item['genreIds'] as $genreId) {
                /** @var array{id:int} $genre */
                foreach ($genres as $genre) {
                    if ($genreId === $genre['id']) {
                        $items[$key]['genres'][] = $genre;
                    }
                }
            }

            if (isset($items[$key]['genreIds'])) {
                unset($items[$key]['genreIds']);
            }
        }

        return $items;
    }

    private function mapUnions(array $items, array $data): array
    {
        /** @var array{array{id:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['union'] = null;
            $items[$key]['unions'] = [];

            /** @var array{id:int, verified:int|null}[] $unions */
            foreach ($data as $albumId => $unions) {
                if ($item['id'] === $albumId) {
                    $items[$key]['union'] = $unions[0];
                    $items[$key]['unions'] = $unions;
                    $items[$key]['likes']['canView'] = (bool)($unions[0]['verified'] ?? 0);
                    break;
                }
            }
        }

        return $items;
    }

    private function mapAdded(array $items, array $liked): array
    {
        /** @var array{array{id:int,isAdded:bool}} $items */
        foreach ($items as $key => $item) {
            if (\in_array($item['id'], $liked, true)) {
                $items[$key]['isAdded'] = true;
            }
        }

        return $items;
    }

    /** @return array{albumIds:int[],genreIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $albumIds = [];
        $genreIds = [];

        /** @var array{id:int,genreIds:int[],unionId:int} $item */
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $albumIds[] = $item['id'];
            }

            if (isset($item['genreIds'])) {
                foreach ($item['genreIds'] as $genreId) {
                    $genreIds[] = $genreId;
                }
            }
        }

        return [
            'albumIds'    => array_unique($albumIds),
            'genreIds'    => array_unique($genreIds),
        ];
    }
}
