<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Notification\GetSubscribes;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionNotificationGetSubscribesFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /** @throws Exception */
    public function fetch(UnionNotificationGetSubscribesQuery $query): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['n.user_id'])
            ->from('union_notification', 'n')
            ->andWhere('n.union_id = :unionId')
            ->setParameter('unionId', $query->unionId);

        /** @var array{array{user_id: int}} $rows */
        $rows = $queryBuilder
            ->setMaxResults(5000)
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];

        foreach ($rows as $row) {
            $result[] = $row['user_id'];
        }

        return $result;
    }
}
