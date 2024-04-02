<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\GetByUnionId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioAlbumGetByUnionIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /** @throws Exception */
    public function fetch(AudioAlbumGetByUnionIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('aa.*')
            ->from('audio_album_union', 'aau')
            ->innerJoin('aau', 'audios_albums', 'aa', 'aau.album_id = aa.id')
            ->where('aau.union_id = :union_id')
            ->andWhere('aa.hide = 0 && aa.deleted_at IS NULL')
            ->setParameter('union_id', $query->unionId);

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('aa.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        if ($query->filter === 'albums') {
            $sqlQuery->andWhere('aa.is_album = 1');
        } elseif ($query->filter === 'singles') {
            $sqlQuery->andWhere('aa.is_album = 0');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('aa.year', $order)
            ->addOrderBy('aa.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'aa.id'), $rows);
    }
}
