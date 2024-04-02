<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByAudioPlaylistId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioGetByAudioPlaylistIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioGetByAudioPlaylistIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audios', 'a')
            ->innerJoin('a', 'audio_playlist_audio', 'apa', 'a.id = apa.audio_id')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->andWhere('apa.playlist_id = :playlistId')
            ->setParameter('playlistId', $query->playlistId);

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('a.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'a.id'), $rows);
    }
}
