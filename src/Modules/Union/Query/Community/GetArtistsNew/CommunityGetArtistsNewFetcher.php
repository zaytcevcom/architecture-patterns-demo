<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetArtistsNew;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class CommunityGetArtistsNewFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(CommunityGetArtistsNewQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions', 'u')
            ->where('u.verified > 0 && is_music = 1')
            ->andWhere('u.photo IS NOT NULL')
            ->andWhere('u.category_id != 112')
            ->andWhere('u.type = :type')
            ->setParameter('type', Union::typeCommunity());

        $result = $sqlQuery
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }
}
