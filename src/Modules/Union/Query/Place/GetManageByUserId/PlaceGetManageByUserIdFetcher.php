<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Place\GetManageByUserId;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class PlaceGetManageByUserIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PlaceGetManageByUserIdQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $roles = [
            (string)Union::roleCreator(),
            (string)Union::roleAdmin(),
            (string)Union::roleEditor(),
            (string)Union::roleModerator(),
        ];

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions', 'u')
            ->innerJoin('u', 'unions_users', 'uu', 'uu.union_id = u.id')
            ->where('uu.user_id = :userId')
            ->andWhere('u.type = :type')
            ->andWhere($queryBuilder->expr()->in('uu.role', $roles))
            ->setParameter('userId', $query->userId)
            ->setParameter('type', Union::typePlace());

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('u.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('u.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }
}
