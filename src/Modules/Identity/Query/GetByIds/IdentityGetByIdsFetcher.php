<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetByIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayInt;
use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class IdentityGetByIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(IdentityGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['*'])
            ->from('users')
            ->where($queryBuilder->expr()->in('id', $ids));

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, toArrayInt($ids));
    }
}
