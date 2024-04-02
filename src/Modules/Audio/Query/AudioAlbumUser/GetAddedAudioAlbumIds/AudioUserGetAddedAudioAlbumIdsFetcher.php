<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbumUser\GetAddedAudioAlbumIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioUserGetAddedAudioAlbumIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(AudioUserGetAddedAudioAlbumIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['a.album_id'])
            ->from('audios_albums_owners', 'a')
            ->andWhere('a.owner_id = :userId')
            ->andWhere($queryBuilder->expr()->in('a.album_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{album_id:int} $row */
        foreach ($rows as $row) {
            $result[] = $row['album_id'];
        }

        return $result;
    }
}
