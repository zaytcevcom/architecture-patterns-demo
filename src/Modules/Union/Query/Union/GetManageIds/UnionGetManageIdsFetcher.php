<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetManageIds;

use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionGetManageIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @return int[]
     * @throws Exception
     */
    public function fetch(UnionGetManageIdsQuery $query): array
    {
        $roles = [
            (string)Union::roleCreator(),
            (string)Union::roleAdmin(),
            (string)Union::roleEditor(),
        ];

        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.id')
            ->from('unions', 'u')
            ->innerJoin('u', 'unions_users', 'uu', 'uu.union_id = u.id')
            ->where('uu.user_id = :userId')
            ->andWhere($queryBuilder->expr()->in('uu.role', $roles))
            ->setParameter('userId', $query->userId);

        $result = $sqlQuery
            ->orderBy('u.count_members', 'DESC')
            ->setMaxResults(10000)
            ->setFirstResult(0)
            ->executeQuery();

        /** @var array{id: int}[] $rows */
        $rows = $result->fetchAllAssociative();

        $result = [];

        foreach ($rows as $row) {
            $result[] = $row['id'];
        }

        return $result;
    }
}
