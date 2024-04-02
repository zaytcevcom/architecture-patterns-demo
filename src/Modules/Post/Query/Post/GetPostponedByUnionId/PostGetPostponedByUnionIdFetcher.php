<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetPostponedByUnionId;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Helpers\CursorPagination\CursorPagination;
use ZayMedia\Shared\Helpers\CursorPagination\CursorPaginationResult;

final readonly class PostGetPostponedByUnionIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PostGetPostponedByUnionIdQuery $query): CursorPaginationResult
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('p.*')
            ->from('wall', 'p')
            ->andWhere('p.owner_id = :unionId')
            ->andWhere('p.hide = 0 && p.deleted_at IS NULL')
            ->andWhere('p.date > 0 && p.date > ' . time())
            ->setParameter('unionId', -1 * $query->unionId);

        $order = ($query->sort === 0) ? 'ASC' : 'DESC';

        return CursorPagination::generateResult(
            query: $sqlQuery,
            cursor: $query->cursor,
            count: $query->count,
            isSortDescending: true,
            orderingBy: [
                'p.date' => $order,
                'p.id' => 'ASC',
            ],
            field: 'p.id',
        );
    }
}
