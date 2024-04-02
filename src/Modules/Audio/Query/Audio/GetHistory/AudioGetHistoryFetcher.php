<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetHistory;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioGetHistoryFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /** @throws Exception */
    public function fetch(AudioGetHistoryQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $subQuery = 'SELECT t.id FROM audio_listen t WHERE t.user_id = :userId GROUP BY t.audio_id ORDER BY MAX(t.time) DESC';

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audio_listen', 'al')
            ->innerJoin('al', 'audios', 'a', 'al.audio_id = a.id')
            ->where('al.id IN (' . $subQuery . ')')
            ->andWhere('al.user_id = :userId')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->setParameter('userId', $query->userId);

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('a.name LIKE :search')
                ->setParameter('search', $query->search . '%');
        }

        $result = $sqlQuery
            ->orderBy('al.time', 'DESC')
            ->addOrderBy('al.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'al.id'), $rows);
    }
}
