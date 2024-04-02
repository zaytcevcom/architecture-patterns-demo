<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetRepostedFlowIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class PostGetRepostedFlowIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(PostGetRepostedFlowIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['p.flow_id'])
            ->from('wall', 'p')
            ->andWhere('p.owner_id = :userId')
            ->andWhere('p.hide = 0 && p.deleted_at IS NULL')
            ->andWhere($queryBuilder->expr()->in('p.flow_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{flow_id:int} $row */
        foreach ($rows as $row) {
            $result[] = $row['flow_id'];
        }

        return $result;
    }
}
