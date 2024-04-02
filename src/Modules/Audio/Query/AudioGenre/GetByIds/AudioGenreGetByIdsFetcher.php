<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioGenre\GetByIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioGenreGetByIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(AudioGenreGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['*'])
            ->from('audios_genre')
            ->where($queryBuilder->expr()->in('id', $ids));

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, $ids);
    }
}
