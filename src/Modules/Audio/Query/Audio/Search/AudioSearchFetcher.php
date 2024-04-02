<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\Search;

use App\Modules\Audio\Service\Typesense\Audio\AudioCollection;
use App\Modules\Audio\Service\Typesense\Audio\AudioQuery;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Exception;
use ZayMedia\Shared\Helpers\Helper;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class AudioSearchFetcher
{
    public function __construct(
        private Connection $connection,
        private AudioCollection $audioCollection,
    ) {}

    /** @throws Exception */
    public function fetch(AudioSearchQuery $query): ResultCountItems
    {
        $ids = toArrayString(
            $this->audioCollection->searchIdentifiers(
                new AudioQuery(
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
            ->from('audios', 'a')
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
