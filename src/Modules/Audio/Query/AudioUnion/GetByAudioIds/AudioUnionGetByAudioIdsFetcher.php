<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioUnion\GetByAudioIds;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioUnionGetByAudioIdsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @return array{audio_id:int, union_id:int}[]
     * @throws Exception
     */
    public function fetch(AudioUnionGetByAudioIdsQuery $query): array
    {
        $ids = toArrayString($query->audioIds);

        if (\count($ids) === 0) {
            return [];
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        $result = $queryBuilder
            ->select(['t.audio_id', 't.union_id'])
            ->from('audio_union', 't')
            ->andWhere($queryBuilder->expr()->in('t.audio_id', $ids))
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        $result = [];

        /** @var array{audio_id:int, union_id:int} $row */
        foreach ($rows as $row) {
            $result[] = [
                'audio_id' => $row['audio_id'],
                'union_id' => $row['union_id'],
            ];
        }

        return $result;
    }
}
