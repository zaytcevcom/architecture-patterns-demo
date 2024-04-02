<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayInt;
use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionGetByIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(UnionGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['*'])
            ->from('unions')
            ->where($queryBuilder->expr()->in('id', $ids));

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, toArrayInt($ids));
    }
}
