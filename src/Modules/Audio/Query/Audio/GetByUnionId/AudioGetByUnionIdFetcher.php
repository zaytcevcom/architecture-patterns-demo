<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByUnionId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioGetByUnionIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /** @throws Exception */
    public function fetch(AudioGetByUnionIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audio_union', 'au')
            ->innerJoin('au', 'audios', 'a', 'au.audio_id = a.id')
            ->where('au.union_id = :unionId')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->setParameter('unionId', $query->unionId);

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
