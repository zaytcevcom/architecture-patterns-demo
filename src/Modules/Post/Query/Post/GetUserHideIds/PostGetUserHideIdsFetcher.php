<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetUserHideIds;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class PostGetUserHideIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PostGetUserHideIdsQuery $query): array
    {
        $sqlQuery = $this->connection->createQueryBuilder()
            ->select(['p.post_id'])
            ->from('post_hide', 'p')
            ->where('p.user_id = :userId')
            ->setParameter('userId', $query->userId);

        $result = $sqlQuery
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(100)
            ->executeQuery();

        $items = [];

        /** @var array{post_id: int} $row */
        foreach ($result->fetchAllAssociative() as $row) {
            $items[] = $row['post_id'];
        }

        return $items;
    }
}
