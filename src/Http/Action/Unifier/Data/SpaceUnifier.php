<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Data;

use App\Modules\Data\Query\City\GetByIds\DataCityGetByIdsFetcherCached;
use App\Modules\Data\Query\City\GetByIds\DataCityGetByIdsQuery;
use App\Modules\Data\Query\Space\GetByIds\DataSpaceGetByIdsFetcher;
use App\Modules\Data\Query\Space\GetByIds\DataSpaceGetByIdsQuery;
use App\Modules\Data\Service\CitySerializer;
use App\Modules\Data\Service\SpaceCitySerializer;
use App\Modules\Data\Service\SpaceSerializer;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class SpaceUnifier implements UnifierInterface
{
    public function __construct(
        private SpaceCitySerializer $spaceCitySerializer,
        private DataCityGetByIdsFetcherCached $cityGetByIdsFetcher,
        private DataSpaceGetByIdsFetcher $spaceGetByIdsFetcher,
        private CitySerializer $citySerializer,
        private SpaceSerializer $spaceSerializer,
        private Translator $translator
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->spaceCitySerializer->serializeItems($items);

        $entityIds = $this->getEntityIds($items);

        $items = $this->mapSpaces($items, $this->getSpaces($entityIds['spaceIds']));

        $items = $this->mapCities($items, $this->getCities($entityIds['cityIds']));

        return $this->sort($items);
    }

    private function sort(array $items): array
    {
        return $items;
    }

    private function getCities(array $ids): array
    {
        return $this->citySerializer->serializeItems(
            $this->cityGetByIdsFetcher->fetch(
                new DataCityGetByIdsQuery($ids, $this->translator->getLocale())
            )
        );
    }

    private function getSpaces(array $ids): array
    {
        return $this->spaceSerializer->serializeItems(
            $this->spaceGetByIdsFetcher->fetch(
                new DataSpaceGetByIdsQuery($ids)
            )
        );
    }

    private function mapCities(array $items, array $cities): array
    {
        /**
         * @var int $key
         * @var array{array{cityId: int|null, cityIds: string[]|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['cities'] = [];

            if (null !== $item['cityId']) {
                /** @var array{id:int, name: string} $city */
                foreach ($cities as $city) {
                    if ($item['cityId'] === $city['id']) {
                        $items[$key]['cities'][] = $city;
                        $items[$key]['name'] = $city['name'];
                        break;
                    }
                }
            }

            if (null !== $item['cityIds']) {
                /** @var int|string $cityId */
                foreach ($item['cityIds'] as $cityId) {
                    /** @var array{id:int, name: string} $city */
                    foreach ($cities as $city) {
                        if ((int)$cityId === $city['id']) {
                            $items[$key]['cities'][] = $city;
                            break;
                        }
                    }
                }
            }
        }

        foreach ($items as $key => $_item) {
            unset($items[$key]['cityId'], $items[$key]['cityIds']);
        }

        return $items;
    }

    private function mapSpaces(array $items, array $spaces): array
    {
        /**
         * @var int $key
         * @var array{spaceId:int|null, cityId:int|null}[] $items
         */
        foreach ($items as $key => $item) {
            /** @var array{id:int, name: string} $space */
            foreach ($spaces as $space) {
                if ($item['spaceId'] === $space['id']) {
                    $items[$key]['name'] = $space['name'];
                    break;
                }
            }

            $items[$key]['group'] = $items[$key]['spaceId'];
            $items[$key]['main'] = null;

            if (isset($item['cityId'])) {
                /** @var array{id:int, name: string} $space */
                foreach ($spaces as $space) {
                    if ($item['spaceId'] === $space['id']) {
                        $items[$key]['main'] = [
                            'id'    => $space['id'],
                            'name'  => $space['name'],
                        ];
                        break;
                    }
                }
            }
        }

        /**
         * @var int $key
         * @var array{spaceId:int|null, cityId:int|null}[] $items
         */
        foreach ($items as $key => $_item) {
            unset($items[$key]['spaceId']);
        }

        return $items;
    }

    /** @return array{spaceIds:int[], cityIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $spaceIds = [];
        $cityIds  = [];

        /** @var array{spaceId:int, cityId:int|null, cityIds: string[]|null} $item */
        foreach ($items as $item) {
            $spaceIds[] = $item['spaceId'];

            if (isset($item['cityId']) && !empty($item['cityId'])) {
                $cityIds[] = $item['cityId'];
            }

            if (isset($item['cityIds'])) {
                foreach ($item['cityIds'] as $cityId) {
                    $cityIds[] = (int)$cityId;
                }
            }
        }

        return [
            'spaceIds' => array_unique($spaceIds),
            'cityIds'  => array_unique($cityIds),
        ];
    }
}
