<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetPopular;

use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioGetPopularFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioGetPopularQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audios', 'a')
            ->andWhere('top > 0')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL');

        $result = $sqlQuery
            ->orderBy('a.top', 'ASC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(\count($rows), $rows);
    }
}
