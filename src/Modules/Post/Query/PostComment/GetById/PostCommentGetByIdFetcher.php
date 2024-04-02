<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\PostComment\GetById;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostCommentGetByIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PostCommentGetByIdQuery $query): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $result = $queryBuilder
            ->select('c.*')
            ->from('post_comment', 'c')
            ->where('c.id = :id')
            ->andWhere('c.deleted_at = 0')
            ->setParameter('id', $query->id)
            ->executeQuery()
            ->fetchAssociative();

        if ($result === false) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.post_comment_not_found',
                code: 1
            );
        }

        return $result;
    }
}
