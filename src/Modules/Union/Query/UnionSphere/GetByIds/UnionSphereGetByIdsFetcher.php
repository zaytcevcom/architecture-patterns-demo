<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionSphere\GetByIds;

use App\Modules\Union\Service\UnionSphereTranslator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionSphereGetByIdsFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionSphereTranslator $translator
    ) {}

    public function fetch(UnionSphereGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (empty($ids)) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['id', 'name'])
            ->from('unions_spheres')
            ->andWhere('id IN (:ids)')
            ->setParameter('ids', $ids, ArrayParameterType::INTEGER);

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        /** @var array{array} $rows */
        $rows = $this->translator->translate($rows, $query->locale);

        return Helper::sortItemsByIds($rows, $ids);
    }
}
