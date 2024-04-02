<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostCommentLike\GetLikedCommentIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class PostCommentLikeGetLikedCommentIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(PostCommentLikeGetLikedCommentIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['p.comment_id'])
            ->from('post_comment_like', 'p')
            ->andWhere('p.user_id = :userId')
            ->andWhere($queryBuilder->expr()->in('p.comment_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{comment_id:int} $row */
        foreach ($rows as $row) {
            $result[] = $row['comment_id'];
        }

        return $result;
    }
}
