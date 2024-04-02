<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\GetByUserId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioPlaylistGetByUserIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioPlaylistGetByUserIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audio_playlist', 'a')
            ->innerJoin('a', 'audio_playlist_user', 'au', 'au.playlist_id = a.id')
            ->where('au.user_id = :userId')
            ->andWhere('a.deleted_at IS NULL')
            ->setParameter('userId', $query->userId);

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
