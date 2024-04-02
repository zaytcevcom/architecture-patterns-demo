<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\New;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioPlaylistNewFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioPlaylistNewQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audio_playlist', 'a')
            ->andWhere('a.deleted_at IS NULL')
            ->andWhere('a.published_at <= :time')
            ->setParameter('time', time());

        $result = $sqlQuery
            ->orderBy('a.updated_at', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'a.id'), $rows);
    }
}
