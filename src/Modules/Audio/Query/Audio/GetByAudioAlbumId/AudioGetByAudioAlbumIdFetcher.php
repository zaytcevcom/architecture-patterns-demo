<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByAudioAlbumId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioGetByAudioAlbumIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioGetByAudioAlbumIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audios', 'a')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->andWhere('album_id = :albumId')
            ->setParameter('albumId', $query->albumId);

        $order = ($query->sort === 0) ? 'ASC' : 'DESC';

        $result = $sqlQuery
            ->orderBy('a.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'a.id'), $rows);
    }
}
