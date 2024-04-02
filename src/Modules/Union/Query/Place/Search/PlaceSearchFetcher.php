<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Place\Search;

use App\Components\AllCount;
use App\Modules\Data\Entity\SpaceCity\SpaceCityRepository;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class PlaceSearchFetcher
{
    public function __construct(
        private Connection $connection,
        private SpaceCityRepository $spaceCityRepository
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(PlaceSearchQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions', 'u')
            ->andWhere('u.photo IS NOT NULL')
            ->andWhere('u.type = :type')
            ->andWhere($queryBuilder->expr()->in('u.city_id', $this->getCityIds($query->spaceId)))
            ->setParameter('type', Union::typePlace());

        if (!empty($query->search)) {
            $sqlQuery
                ->andWhere('u.name LIKE :search')
                ->setParameter('search', '%' . $query->search . '%');
        }

        if ($query->categoryId !== null) {
            $sqlQuery
                ->andWhere('u.category_id = :categoryId')
                ->setParameter('categoryId', $query->categoryId);
        } elseif ($query->sphereId !== null) {
            $sqlQuery
                ->innerJoin('u', 'unions_spheres_categories', 'c', 'c.id = u.category_id')
                ->andWhere('c.sphere_id = :sphereId')
                ->setParameter('sphereId', $query->sphereId);
        }

        $result = $sqlQuery
            ->orderBy('u.count_members', 'DESC')
            ->setMaxResults($query->count)
            ->setFirstResult($query->offset)
            ->executeQuery();

        $rows = $result->fetchAllAssociative();

        return new ResultCountItems(AllCount::get($sqlQuery, 'u.id'), $rows);
    }

    /** @return string[] */
    private function getCityIds(int $spaceId): array
    {
        $spaceCity = $this->spaceCityRepository->getById($spaceId);

        if ($cityId = $spaceCity->getCityId()) {
            return [(string)$cityId];
        }

        $cityIds = $spaceCity->getCityIds() ?? [];

        return toArrayString($cityIds);
    }
}
