<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\GetByIds;

use Doctrine\DBAL\Connection;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioAlbumGetByIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(AudioAlbumGetByIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(['*'])
            ->from('audios_albums')
            ->where($queryBuilder->expr()->in('id', $ids));

        /** @var array{array} $rows */
        $rows = $queryBuilder
            ->setMaxResults(1000)
            ->executeQuery()
            ->fetchAllAssociative();

        return Helper::sortItemsByIds($rows, $ids);
    }
}
