<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\GetByUnionId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioPlaylistGetByUnionIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioPlaylistGetByUnionIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audio_playlist', 'a')
            ->where('a.union_id = :union_id')
            ->andWhere('a.deleted_at IS NULL')
            ->setParameter('union_id', $query->unionId);

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('a.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

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
