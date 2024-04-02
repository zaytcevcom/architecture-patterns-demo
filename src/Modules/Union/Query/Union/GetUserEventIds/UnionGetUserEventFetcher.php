<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetUserEventIds;

use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionGetUserEventFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionGetUserEventQuery $query): array
    {
        $sqlQuery = $this->connection->createQueryBuilder()
            ->select(['u.id'])
            ->from('unions', 'u')
            ->innerJoin('u', 'unions_users', 'uu', 'uu.union_id = u.id')
            ->where('uu.user_id = :userId')
            ->andWhere('u.type = :type')
            ->setParameter('userId', $query->userId)
            ->setParameter('type', Union::typeEvent());

        $result = $sqlQuery
            ->executeQuery();

        $items = [];

        /** @var array{id: int} $row */
        foreach ($result->fetchAllAssociative() as $row) {
            $items[] = $row['id'];
        }

        return $items;
    }
}
