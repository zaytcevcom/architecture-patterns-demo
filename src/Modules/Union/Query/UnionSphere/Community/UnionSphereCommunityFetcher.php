<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\Community;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Service\UnionSphereTranslator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionSphereCommunityFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionSphereTranslator $translator
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionSphereCommunityQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions_spheres', 'u')
            ->where('u.union_type = :type')
            ->setParameter('type', Union::typeCommunity());

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('u.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('u.name', $order)
            ->addOrderBy('u.id', 'ASC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $rows = $this->translator->translate($rows, $query->locale);

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }
}
