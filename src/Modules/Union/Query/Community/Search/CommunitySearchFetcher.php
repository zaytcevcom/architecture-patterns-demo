<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\Search;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Service\Typesense\Community\CommunityCollection;
use App\Modules\Union\Service\Typesense\Community\CommunityQuery;
use Doctrine\DBAL\Connection;
use Exception;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class CommunitySearchFetcher
{
    public function __construct(
        private Connection $connection,
        private CommunityCollection $communityCollection
    ) {}

    /** @throws Exception */
    public function fetch(CommunitySearchQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions', 'u')
            ->andWhere('u.photo IS NOT NULL')
            ->andWhere('u.type = :type')
            ->setParameter('type', Union::typeCommunity());

        $ids = toArrayString(
            $this->communityCollection->searchIdentifiers(
                new CommunityQuery(
                    search: $query->search,
                    sphereId: $query->sphereId,
                    categoryId: $query->categoryId,
                    categoryKind: $query->categoryKind,
                )
            )
        );

        if (\count($ids) === 0) {
            return new ResultCountItems(0, []);
        }

        $sqlQuery->andWhere(
            $queryBuilder->expr()->in('u.id', $ids)
        );

        $result = $sqlQuery
            ->orderBy('u.count_members', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        /** @var array{array} $rows */
        $rows = $result->fetchAllAssociative();

        $rows = Helper::sortItemsByIds($rows, $ids);

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }
}
