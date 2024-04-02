<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionCategory\Community\All;

use App\Components\AllCount;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Service\UnionCategoryTranslator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class UnionCategoryCommunityFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionCategoryTranslator $translator
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionCategoryCommunityQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $filter = match ($query->filter) {
            'musical'   => 1,
            'usual'     => 0,
            default     => null
        };

        $kind = (null !== $filter) ? ' && c.kind = ' . $filter : '';

        $sqlQuery = $queryBuilder
            ->select('c.*')
            ->from('unions_categories', 'c')
            ->leftJoin('c', 'unions_spheres_categories', 'uc', 'c.id = uc.category_id')
            ->leftJoin('uc', 'unions_spheres', 'us', 'us.id = uc.sphere_id')
            ->where('us.union_type = :type' . $kind)
            ->setParameter('type', Union::typeCommunity());

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('c.name', $order)
            ->addOrderBy('c.id', 'ASC')
            ->distinct()
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $rows = $this->translator->translate($rows, $query->locale);

        return new ResultCountItems(AllCount::get($sqlQuery, 'c.id'), $rows);
    }
}
