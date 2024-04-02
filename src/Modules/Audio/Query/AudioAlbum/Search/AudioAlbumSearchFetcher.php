<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\Search;

use App\Modules\Audio\Service\Typesense\AudioAlbum\AudioAlbumCollection;
use App\Modules\Audio\Service\Typesense\AudioAlbum\AudioAlbumQuery;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Exception;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioAlbumSearchFetcher
{
    public function __construct(
        private Connection $connection,
        private AudioAlbumCollection $audioAlbumCollection,
    ) {}

    /** @throws Exception */
    public function fetch(AudioAlbumSearchQuery $query): ResultCountItems
    {
        $isAlbum = null;

        if (null !== $query->filter) {
            $isAlbum = ($query->filter === 'albums');
        }

        $ids = toArrayString(
            $this->audioAlbumCollection->searchIdentifiers(
                new AudioAlbumQuery(
                    search: $query->search,
                    isAlbum: $isAlbum
                )
            )
        );

        if (\count($ids) === 0) {
            return new ResultCountItems(0, []);
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('a.*')
            ->from('audios_albums', 'a')
            ->andWhere('a.hide = 0 && a.deleted_at IS NULL')
            ->andWhere($queryBuilder->expr()->in('a.id', $ids));

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('a.id', $order)
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        /** @var array{array} $rows */
        $rows = $result->fetchAllAssociative();

        $rows = Helper::sortItemsByIds($rows, $ids);

        return new ResultCountItems(\count($rows), $rows);
    }
}
