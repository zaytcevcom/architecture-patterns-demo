<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylistUser\GetAddedAudioPlaylistIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioUserGetAddedAudioPlaylistIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(AudioUserGetAddedAudioPlaylistIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['a.playlist_id'])
            ->from('audio_playlist_user', 'a')
            ->andWhere('a.user_id = :userId')
            ->andWhere($queryBuilder->expr()->in('a.playlist_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{playlist_id:int} $row */
        foreach ($rows as $row) {
            $result[] = $row['playlist_id'];
        }

        return $result;
    }
}
