<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Member\GetMemberedUnionIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionGetMemberedUnionIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(UnionGetMemberedUnionIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['uu.union_id', 'uu.role'])
            ->from('unions_users', 'uu')
            ->andWhere('uu.user_id = :userId')
            ->andWhere($queryBuilder->expr()->in('uu.union_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{union_id: int, role: int} $row */
        foreach ($rows as $row) {
            $result[$row['union_id']] = $row['role'];
        }

        return $result;
    }
}
