<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Audio;

use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsFetcher;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsQuery;
use App\Modules\Audio\Query\AudioUnion\GetByAudioIds\AudioUnionGetByAudioIdsFetcher;
use App\Modules\Audio\Query\AudioUnion\GetByAudioIds\AudioUnionGetByAudioIdsQuery;
use App\Modules\Audio\Query\AudioUser\GetAddedAudioIds\AudioUserGetAddedAudioIdsFetcher;
use App\Modules\Audio\Query\AudioUser\GetAddedAudioIds\AudioUserGetAddedAudioIdsQuery;
use App\Modules\Audio\Service\AudioAlbumSerializer;
use App\Modules\Audio\Service\AudioSerializer;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsFetcherCached;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsQuery;
use App\Modules\Union\Service\UnionSerializer;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class AudioUnifier implements UnifierInterface
{
    public function __construct(
        private AudioSerializer $audioSerializer,
        private AudioAlbumGetByIdsFetcher $audioAlbumGetByIdsFetcher,
        private AudioAlbumSerializer $audioAlbumSerializer,
        private UnionGetByIdsFetcherCached $unionGetByIdsFetcher,
        private UnionSerializer $unionSerializer,
        private AudioUserGetAddedAudioIdsFetcher $audioUserGetAddedAudioIdsFetcher,
        private AudioUnionGetByAudioIdsFetcher $audioUnionGetByAudioIdsFetcher,
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->audioSerializer->serializeItems($items);

        $entityIds = $this->getEntityIds($items);

        $items = $this->mapAlbums($items, $this->getAlbums($entityIds['albumIds']));

        $items = $this->mapUnions($items, $this->getAudioUnions($entityIds['audioIds']));

        if (null !== $userId) {
            $items = $this->mapAdded($items, $this->getAdded($userId, $entityIds['audioIds']));
        }

        return $items;
    }

    private function getAlbums(array $ids): array
    {
        return $this->audioAlbumSerializer->serializeItems(
            $this->audioAlbumGetByIdsFetcher->fetch(
                new AudioAlbumGetByIdsQuery($ids)
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

    private function getAudioUnions(array $ids): array
    {
        $audioUnions = $this->audioUnionGetByAudioIdsFetcher->fetch(
            new AudioUnionGetByAudioIdsQuery($ids)
        );

        $unionIds = [];

        foreach ($audioUnions as $albumUnion) {
            $unionIds[] = $albumUnion['union_id'];
        }

        $unions = $this->getUnions($unionIds);

        $result = [];

        foreach ($audioUnions as $audioUnion) {
            /** @var array{id: int} $union */
            foreach ($unions as $union) {
                if ($audioUnion['union_id'] === $union['id']) {
                    if (!isset($result[$audioUnion['audio_id']])) {
                        $result[$audioUnion['audio_id']] = [];
                    }
                    $result[$audioUnion['audio_id']][] = $union;
                    break;
                }
            }
        }

        return $result;
    }

    private function getAdded(int $userId, array $ids): array
    {
        $query = new AudioUserGetAddedAudioIdsQuery(
            userId: $userId,
            ids: $ids
        );

        return $this->audioUserGetAddedAudioIdsFetcher->fetch($query);
    }

    private function mapAlbums(array $items, array $albums): array
    {
        /** @var array{array{albumId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['album'] = null;

            if (null !== $item['albumId']) {
                /** @var array{id:int} $album */
                foreach ($albums as $album) {
                    if ($item['albumId'] === $album['id']) {
                        $items[$key]['album'] = $album;
                        break;
                    }
                }
            }

            if (isset($items[$key]['albumId'])) {
                unset($items[$key]['albumId']);
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
            foreach ($data as $audioId => $unions) {
                if ($item['id'] === $audioId) {
                    $items[$key]['union'] = $unions[0];
                    $items[$key]['unions'] = $unions;
                    $items[$key]['likes']['canView'] = (bool)($unions[0]['verified'] ?? 0);
                    $items[$key]['likes']['canLike'] = (bool)($unions[0]['verified'] ?? 0);
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
                $items[$key]['likes']['isLiked'] = true;
            }
        }

        return $items;
    }

    /** @return array{audioIds:int[],albumIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $audioIds = [];
        $albumIds = [];

        /** @var array{id:int,albumId:int,unionId:int} $item */
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $audioIds[] = $item['id'];
            }

            if (!empty($item['albumId'])) {
                $albumIds[] = $item['albumId'];
            }
        }

        return [
            'audioIds'    => array_unique($audioIds),
            'albumIds'    => array_unique($albumIds),
        ];
    }
}
