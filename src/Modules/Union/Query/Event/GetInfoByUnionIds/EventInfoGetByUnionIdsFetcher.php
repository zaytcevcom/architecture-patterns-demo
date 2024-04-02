<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetInfoByUnionIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayInt;
use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class EventInfoGetByUnionIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(EventInfoGetByUnionIdsQuery $query): array
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
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, toArrayInt($ids), 'union_id');
    }
}
