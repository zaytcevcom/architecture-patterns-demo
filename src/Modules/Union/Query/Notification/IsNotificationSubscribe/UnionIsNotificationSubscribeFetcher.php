<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Notification\IsNotificationSubscribe;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionIsNotificationSubscribeFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionIsNotificationSubscribeQuery $query): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['count(id) as count'])
            ->from('union_notification')
            ->where('user_id = :userId')
            ->andWhere('union_id = :unionId')
            ->setParameter('userId', $query->userId)
            ->setParameter('unionId', $query->unionId)
            ->setFirstResult(0)
            ->fetchAssociative();

        return (bool)($result['count'] ?? 0);
    }
}
