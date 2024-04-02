<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetCompletedByUnionId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class EventGetCompletedByUnionIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(EventGetCompletedByUnionIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $time = time();

        $sqlQuery = $queryBuilder
            ->select(['u.*', 'ue.id AS event_id'])
            ->from('unions_events', 'ue')
            ->leftJoin('ue', 'unions', 'u', 'u.id = ue.union_id')
            ->where('-ue.owner_id = :unionId')
            ->andWhere('u.type = :type')
            ->andWhere('ue.time_end <= :time')
            ->setParameter('unionId', $query->unionId)
            ->setParameter('time', $time)
            ->setParameter('type', Union::typeEvent());

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('u.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $sqlQuery->distinct();

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('ue.time_start', $order)
            ->addOrderBy('u.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'ue.id'), $rows);
    }
}
