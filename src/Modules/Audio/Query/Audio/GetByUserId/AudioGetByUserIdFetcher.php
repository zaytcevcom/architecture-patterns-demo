<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByUserId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class AudioGetByUserIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioGetByUserIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        // todo: use fields "userId"

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audios', 'a')
            ->innerJoin('a', 'audios_owners', 'au', 'au.audio_id = a.id')
            ->where('au.owner_id = :userId')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->setParameter('userId', $query->userId);

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('a.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('a.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'a.id'), $rows);
    }
}
