<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Audio;

use App\Modules\Audio\Query\AudioPlaylistUser\GetAddedAudioPlaylistIds\AudioUserGetAddedAudioPlaylistIdsFetcher;
use App\Modules\Audio\Query\AudioPlaylistUser\GetAddedAudioPlaylistIds\AudioUserGetAddedAudioPlaylistIdsQuery;
use App\Modules\Audio\Service\AudioPlaylistSerializer;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsFetcherCached;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsQuery;
use App\Modules\Union\Service\UnionSerializer;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class AudioPlaylistUnifier implements UnifierInterface
{
    public function __construct(
        private AudioPlaylistSerializer $audioPlaylistSerializer,
        private UnionGetByIdsFetcherCached $unionGetByIdsFetcher,
        private UnionSerializer $unionSerializer,
        private AudioUserGetAddedAudioPlaylistIdsFetcher $audioUserGetAddedAudioPlaylistIdsFetcher
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->audioPlaylistSerializer->serializeItems($items);

        $entityIds = $this->getEntityIds($items);

        $items = $this->mapUnions($items, $this->getUnions($entityIds['unionIds']));

        if (null === $userId) {
            return $items;
        }

        return $this->mapAdded($items, $this->getAdded($userId, $entityIds['playlistIds']));
    }

    private function getUnions(array $ids): array
    {
        return $this->unionSerializer->serializeItems(
            $this->unionGetByIdsFetcher->fetch(
                new UnionGetByIdsQuery($ids)
            )
        );
    }

    private function getAdded(int $userId, array $ids): array
    {
        return $this->audioUserGetAddedAudioPlaylistIdsFetcher->fetch(
            new AudioUserGetAddedAudioPlaylistIdsQuery($userId, $ids)
        );
    }

    private function mapUnions(array $items, array $unions): array
    {
        /** @var array{array{unionId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['union'] = null;
            $items[$key]['unions'] = [];

            if (null !== $item['unionId']) {
                /** @var array{id:int} $union */
                foreach ($unions as $union) {
                    if ($item['unionId'] === $union['id']) {
                        $items[$key]['union'] = $union;
                        $items[$key]['unions'][] = $union;
                        break;
                    }
                }
            }

            if (isset($items[$key]['unionId'])) {
                unset($items[$key]['unionId']);
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

    /** @return array{playlistIds:int[],unionIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $playlistIds = [];
        $unionIds = [];

        /** @var array{id:int,unionId:int} $item */
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $playlistIds[] = $item['id'];
            }

            if (isset($item['unionId']) && !empty($item['unionId'])) {
                $unionIds[] = $item['unionId'];
            }
        }

        return [
            'playlistIds' => array_unique($playlistIds),
            'unionIds'    => array_unique($unionIds),
        ];
    }
}
