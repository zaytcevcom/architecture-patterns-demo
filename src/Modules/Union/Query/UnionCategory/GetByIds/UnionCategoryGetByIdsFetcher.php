<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\UnionCategory\GetByIds;

use App\Modules\Union\Service\UnionCategoryTranslator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionCategoryGetByIdsFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionCategoryTranslator $translator
    ) {}

    public function fetch(UnionCategoryGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (empty($ids)) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['id', 'name'])
            ->from('unions_categories')
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
