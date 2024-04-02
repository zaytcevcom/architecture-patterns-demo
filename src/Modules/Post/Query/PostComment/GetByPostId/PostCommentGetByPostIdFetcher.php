<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetByPostId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class PostCommentGetByPostIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PostCommentGetByPostIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('c.*')
            ->from('post_comment', 'c')
            ->andWhere('c.post_id = :postId')
            ->andWhere('c.deleted_at IS NULL')
            ->setParameter('postId', $query->postId);

        if ($query->commentId !== null) {
            $sqlQuery
                ->andWhere('c.comment_id = :commentId')
                ->setParameter('commentId', $query->commentId);
        } else {
            $sqlQuery
                ->andWhere('c.comment_id IS NULL');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('c.created_at', $order)
            ->addOrderBy('c.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery), $rows);
    }
}
