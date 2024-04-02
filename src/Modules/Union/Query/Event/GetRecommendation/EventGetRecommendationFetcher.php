<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetRecommendation;

use App\Components\AllCount;
use App\Modules\Data\Entity\SpaceCity\SpaceCityRepository;
use App\Modules\ResultCountItems;
use App\Modules\Union\Entity\Union\Union;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class EventGetRecommendationFetcher
{
    public function __construct(
        private Connection $connection,
        private SpaceCityRepository $spaceCityRepository
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(EventGetRecommendationQuery $query): ResultCountItems
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $sqlQuery = $queryBuilder
            ->select('u.*')
            ->from('unions', 'u')
            ->where('u.recommendation = 1')
            ->andWhere('u.photo IS NOT NULL')
            ->andWhere('u.type = :type')
            ->andWhere($queryBuilder->expr()->in('u.city_id', $this->getCityIds($query->spaceId)))
            ->setParameter('type', Union::typeEvent());

        $order = ($query->sort === 0) ? 'DESC' : 'ASC';

        $result = $sqlQuery
            ->orderBy('u.id', $order)
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
