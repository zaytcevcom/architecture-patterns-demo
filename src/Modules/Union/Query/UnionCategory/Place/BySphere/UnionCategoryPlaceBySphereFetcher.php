<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionCategory\Place\BySphere;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Service\UnionCategoryTranslator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionCategoryPlaceBySphereFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionCategoryTranslator $translator
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionCategoryPlaceBySphereQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('c.*')
            ->from('unions_categories', 'c')
            ->leftJoin('c', 'unions_spheres_categories', 'uc', 'c.id = uc.category_id')
            ->leftJoin('uc', 'unions_spheres', 'us', 'us.id = uc.sphere_id')
            ->where('us.union_type = :type')
//            ->andWhere('c.union_count > 5')
            ->andWhere('us.id = :sphereId')
            ->setParameter('type', Union::typePlace())
            ->setParameter('sphereId', $query->sphereId);

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $sqlQuery->distinct();

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('c.name', $order)
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $rows = $this->translator->translate($rows, $query->locale);

        return new ResultCountItems(AllCount::get($sqlQuery, 'c.id'), $rows);
    }
}
