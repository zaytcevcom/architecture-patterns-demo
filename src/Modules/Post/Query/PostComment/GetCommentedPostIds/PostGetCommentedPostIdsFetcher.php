<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetCommentedPostIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class PostGetCommentedPostIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(PostGetCommentedPostIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['p.post_id'])
            ->from('post_comment', 'p')
            ->andWhere('p.user_id = :userId')
            ->andWhere($queryBuilder->expr()->in('p.post_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{post_id:int} $row */
        foreach ($rows as $row) {
            $result[] = $row['post_id'];
        }

        return $result;
    }
}
