<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\Search;

use App\Modules\Audio\Service\Typesense\AudioPlaylist\AudioPlaylistCollection;
use App\Modules\Audio\Service\Typesense\AudioPlaylist\AudioPlaylistQuery;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Exception;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioPlaylistSearchFetcher
{
    public function __construct(
        private Connection $connection,
        private AudioPlaylistCollection $audioPlaylistCollection
    ) {}

    /** @throws Exception */
    public function fetch(AudioPlaylistSearchQuery $query): ResultCountItems
    {
        $ids = toArrayString(
            $this->audioPlaylistCollection->searchIdentifiers(
                new AudioPlaylistQuery(
                    search: $query->search
                )
            )
        );

        if (\count($ids) === 0) {
            return new ResultCountItems(0, []);
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audio_playlist', 'a')
            ->andWhere('a.deleted_at IS NULL')
            ->andWhere('a.published_at <= :time')
            ->andWhere($queryBuilder->expr()->in('a.id', $ids))
            ->setParameter('time', time());

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('a.updated_at', 'DESC')
            ->addOrderBy('a.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        /** @var array{array} $rows */
        $rows = $result->fetchAllAssociative();

        $rows = Helper::sortItemsByIds($rows, $ids);

        return new ResultCountItems(\count($rows), $rows);
    }
}
