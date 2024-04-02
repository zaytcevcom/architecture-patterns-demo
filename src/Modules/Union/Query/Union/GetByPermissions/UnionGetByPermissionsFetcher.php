<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetByPermissions;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionGetByPermissionsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionGetByPermissionsQuery $query): ResultCountItems
    {
        $roles = [
            (string)Union::roleCreator(),
            (string)Union::roleAdmin(),
            (string)Union::roleEditor(),
        ];

        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions', 'u')
            ->innerJoin('u', 'unions_users', 'uu', 'uu.union_id = u.id')
            ->where('uu.user_id = :userId')
            ->andWhere($queryBuilder->expr()->in('uu.role', $roles))
            ->setParameter('userId', $query->userId);

        $result = $sqlQuery
            ->orderBy('u.count_members', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }
}
