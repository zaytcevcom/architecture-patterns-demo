<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetByHashtag;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class PostGetByHashtagFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PostGetByHashtagQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $hashtag = '#' . str_replace(['#', '/', ' '], '', $query->hashtag);

        $sqlQuery = $queryBuilder
            ->select('p.*')
            ->from('wall', 'p')
            ->andWhere('p.hide = 0 && p.deleted_at IS NULL')
            ->andWhere('p.date > 0 && p.date <= ' . time())
            ->andWhere('p.message LIKE :hashtag')
            ->setParameter('hashtag', '%' . $hashtag . '%');

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->addOrderBy('p.date', $order)
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery), $rows);
    }
}
