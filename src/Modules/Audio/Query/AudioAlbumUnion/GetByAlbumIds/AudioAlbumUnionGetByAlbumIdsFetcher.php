<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbumUnion\GetByAlbumIds;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioAlbumUnionGetByAlbumIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @return array{album_id:int, union_id:int}[]
     * @throws Exception
     */
    public function fetch(AudioAlbumUnionGetByAlbumIdsQuery $query): array
    {
        $ids = toArrayString($query->albumIds);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['t.album_id', 't.union_id'])
            ->from('audio_album_union', 't')
            ->andWhere($queryBuilder->expr()->in('t.album_id', $ids))
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{album_id:int, union_id:int} $row */
        foreach ($rows as $row) {
            $result[] = [
                'album_id' => $row['album_id'],
                'union_id' => $row['union_id'],
            ];
        }

        return $result;
    }
}
