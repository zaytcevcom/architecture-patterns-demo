<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Member\Get;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionMemberGetFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionMemberGetQuery $query): ResultCountItems
    {
        $roles = [
            (string)Union::roleCreator(),
            (string)Union::roleAdmin(),
            (string)Union::roleEditor(),
            (string)Union::roleModerator(),
            (string)Union::roleMember(),
        ];

        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select([
                'u.*',
                'uu.role',
                'uu.time_join',
            ])
            ->from('unions_users', 'uu')
            ->innerJoin('uu', 'users', 'u', 'uu.user_id = u.id')
            ->where('uu.union_id = :unionId')
            ->andWhere($queryBuilder->expr()->in('uu.role', $roles))
            ->setParameter('unionId', $query->unionId);

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('u.first_name LIKE :search || u.last_name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $result = $sqlQuery
            ->orderBy('uu.time_join', 'DESC')
            ->addOrderBy('u.id', 'ASC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }
}
