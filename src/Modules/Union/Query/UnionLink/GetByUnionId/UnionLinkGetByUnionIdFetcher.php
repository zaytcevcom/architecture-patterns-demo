<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionLink\GetByUnionId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionLinkGetByUnionIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionLinkGetByUnionIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('*')
            ->from('unions_links')
            ->where('union_id = :unionId')
            ->andWhere('deleted_at IS NULL')
            ->setParameter('unionId', $query->unionId);

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery), $rows);
    }
}
