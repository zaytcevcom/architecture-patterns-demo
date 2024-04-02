<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\GetByCategoryIds;

use App\Modules\Union\Service\UnionSphereTranslator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionSphereGetByCategoryIdsFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionSphereTranslator $translator
    ) {}

    public function fetch(UnionSphereGetByCategoryIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (empty($ids)) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['s.id', 's.name', 'sc.category_id'])
            ->from('unions_spheres', 's')
            ->innerJoin('s', 'unions_spheres_categories', 'sc', 's.id = sc.sphere_id')
            ->andWhere('sc.category_id IN (:ids)')
            ->setParameter('ids', $ids, ArrayParameterType::INTEGER);

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        /** @var array{array} $rows */
        $rows = $this->translator->translate($rows, $query->locale);

        return Helper::sortItemsByIds($rows, $ids, 'category_id');
    }
}
