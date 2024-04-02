<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetByIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class PostGetByIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(PostGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['*'])
            ->from('wall')
            ->where($queryBuilder->expr()->in('id', $ids))
            ->andWhere($queryBuilder->expr()->eq('hide', 0))
            ->andWhere($queryBuilder->expr()->isNull('deleted_at'));

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, $ids);
    }
}
