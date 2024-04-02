<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioUser\GetAddedAudioIds;

use Doctrine\DBAL\Connection;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioUserGetAddedAudioIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(AudioUserGetAddedAudioIdsQuery $query): array
    {
        $ids = toArrayString($query->ids);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['a.audio_id'])
            ->from('audios_owners', 'a')
            ->andWhere('a.owner_id = :userId')
            ->andWhere($queryBuilder->expr()->in('a.audio_id', $ids))
            ->setParameter('userId', $query->userId)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{audio_id:int} $row */
        foreach ($rows as $row) {
            $result[] = $row['audio_id'];
        }

        return $result;
    }
}
