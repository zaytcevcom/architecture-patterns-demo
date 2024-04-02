<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\New;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioAlbumNewFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioAlbumNewQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audios_albums', 'a')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL');

        if ($query->filter === 'albums') {
            $sqlQuery->andWhere('a.is_album = 1');
        } elseif ($query->filter === 'singles') {
            $sqlQuery->andWhere('a.is_album = 0');
        }

        $result = $sqlQuery
            ->orderBy('a.year', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'a.id'), $rows);
    }
}
