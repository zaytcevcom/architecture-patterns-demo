<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetRequestsCount;

use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionGetRequestsCountFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /** @throws Exception */
    public function fetch(int $userId): int
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['count(user_id) as count'])
            ->from('unions_users')
            ->where('user_id = :userId')
            ->andWhere('role = :role')
            ->setParameter('userId', $userId)
            ->setParameter('role', Union::roleInvite())
            ->setFirstResult(0)
            ->fetchAssociative();

        return (int)($result['count'] ?? 0);
    }
}
