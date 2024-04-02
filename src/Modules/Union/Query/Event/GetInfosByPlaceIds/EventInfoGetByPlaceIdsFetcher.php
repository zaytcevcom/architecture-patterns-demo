<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetInfosByPlaceIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class EventInfoGetByPlaceIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(EventInfoGetByPlaceIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['*'])
            ->from('unions_events')
            ->where($queryBuilder->expr()->in('union_id', $ids));

        /** @var array{array} $rows */
        return $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
