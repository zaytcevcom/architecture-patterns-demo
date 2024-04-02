<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetLiked;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Helpers\CursorPagination\CursorPagination;
use ZayMedia\Shared\Helpers\CursorPagination\CursorPaginationResult;

final readonly class PostGetLikedFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /** @throws Exception */
    public function fetch(PostGetLikedQuery $query): CursorPaginationResult
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('p.*')
            ->from('wall', 'p')
            ->innerJoin('p', 'post_like', 'pl', 'p.id = pl.post_id')
            ->andWhere('pl.user_id = :userId')
            ->andWhere('p.hide = 0 && p.deleted_at IS NULL')
            ->andWhere('p.date > 0 && p.date < ' . time())
            ->setParameter('userId', $query->userId);

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        return CursorPagination::generateResult(
            query: $sqlQuery,
            cursor: $query->cursor,
            count: $query->count,
            isSortDescending: true,
            orderingBy: [
                'p.date' => $order,
                'p.id' => 'DESC',
            ],
            field: 'p.id',
        );
    }
}
