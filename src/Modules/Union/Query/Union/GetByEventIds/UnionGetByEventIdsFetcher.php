<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByEventIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayInt;
use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionGetByEventIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(UnionGetByEventIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select([
                'u.*',
                'ue.union_id AS union_id',
            ])
            ->from('unions', 'u')
            ->innerJoin('u', 'unions_events', 'ue', '-ue.owner_id = u.id')
            ->where($queryBuilder->expr()->in('ue.union_id', $ids));

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, toArrayInt($ids), 'union_id');
    }
}
