<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\Search;

use App\Modules\Identity\Service\Typesense\User\UserCollection;
use App\Modules\Identity\Service\Typesense\User\UserQuery;
use App\Modules\ResultCountItems;
use Doctrine\DBAL\Connection;
use Exception;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class IdentitySearchFetcher
{
    public function __construct(
        private Connection $connection,
        private UserCollection $userCollection,
    ) {}

    /** @throws Exception */
    public function fetch(IdentitySearchQuery $query): ResultCountItems
    {
        $ids = toArrayString(
            $this->userCollection->searchIdentifiers(
                new UserQuery(
                    search: $query->search,
                    countryId: $query->countryId,
                    cityId: $query->cityId,
                    marital: $query->marital,
                    sex: $query->sex,
                    ageFrom: $query->ageFrom,
                    ageTo: $query->ageTo
                )
            )
        );

        if (\count($ids) === 0) {
            return new ResultCountItems(0, []);
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select(['u.*'])
            ->from('users', 'u')
            ->andWhere($queryBuilder->expr()->in('u.id', $ids));

        $result = $sqlQuery
            ->orderBy('u.id', 'ASC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(\count($rows), $rows);
    }
}
