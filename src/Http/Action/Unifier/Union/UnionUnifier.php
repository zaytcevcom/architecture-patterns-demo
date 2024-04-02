<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Union;

use App\Modules\Data\Query\City\GetByIds\DataCityGetByIdsFetcherCached;
use App\Modules\Data\Query\City\GetByIds\DataCityGetByIdsQuery;
use App\Modules\Data\Query\Country\GetByIds\DataCountryGetByIdsFetcherCached;
use App\Modules\Data\Query\Country\GetByIds\DataCountryGetByIdsQuery;
use App\Modules\Data\Service\CitySerializer;
use App\Modules\Data\Service\CountrySerializer;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Query\Community\GetSections\CommunityGetSectionsFetcher;
use App\Modules\Union\Query\Community\GetSections\CommunityGetSectionsQuery;
use App\Modules\Union\Query\Event\GetInfoByIds\EventInfoGetByIdsFetcher;
use App\Modules\Union\Query\Event\GetInfoByIds\EventInfoGetByIdsQuery;
use App\Modules\Union\Query\Event\GetInfoByUnionIds\EventInfoGetByUnionIdsFetcher;
use App\Modules\Union\Query\Event\GetInfoByUnionIds\EventInfoGetByUnionIdsQuery;
use App\Modules\Union\Query\Event\GetInfosByPlaceIds\EventInfoGetByPlaceIdsFetcher;
use App\Modules\Union\Query\Event\GetInfosByPlaceIds\EventInfoGetByPlaceIdsQuery;
use App\Modules\Union\Query\Member\GetMemberedUnionIds\UnionGetMemberedUnionIdsFetcher;
use App\Modules\Union\Query\Member\GetMemberedUnionIds\UnionGetMemberedUnionIdsQuery;
use App\Modules\Union\Query\Notification\IsNotificationSubscribe\UnionIsNotificationSubscribeFetcher;
use App\Modules\Union\Query\Notification\IsNotificationSubscribe\UnionIsNotificationSubscribeQuery;
use App\Modules\Union\Query\Place\GetInfoByIds\PlaceInfoGetByUnionIdsFetcher;
use App\Modules\Union\Query\Place\GetInfoByIds\PlaceInfoGetByUnionIdsQuery;
use App\Modules\Union\Query\Union\GetByEventIds\UnionGetByEventIdsFetcher;
use App\Modules\Union\Query\Union\GetByEventIds\UnionGetByEventIdsQuery;
use App\Modules\Union\Query\UnionCategory\GetByIds\UnionCategoryGetByIdsFetcher;
use App\Modules\Union\Query\UnionCategory\GetByIds\UnionCategoryGetByIdsQuery;
use App\Modules\Union\Query\UnionSphere\GetByCategoryIds\UnionSphereGetByCategoryIdsFetcher;
use App\Modules\Union\Query\UnionSphere\GetByCategoryIds\UnionSphereGetByCategoryIdsQuery;
use App\Modules\Union\Query\UnionSphere\GetByIds\UnionSphereGetByIdsFetcher;
use App\Modules\Union\Query\UnionSphere\GetByIds\UnionSphereGetByIdsQuery;
use App\Modules\Union\Service\UnionCategorySerializer;
use App\Modules\Union\Service\UnionEventInfoSerializer;
use App\Modules\Union\Service\UnionPlaceInfoSerializer;
use App\Modules\Union\Service\UnionSerializer;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final readonly class UnionUnifier implements UnifierInterface
{
    public function __construct(
        private UnionSerializer $unionSerializer,
        private DataCountryGetByIdsFetcherCached $countryFetcher,
        private CountrySerializer $countrySerializer,
        private DataCityGetByIdsFetcherCached $cityFetcher,
        private CitySerializer $citySerializer,
        private UnionGetByEventIdsFetcher $unionGetByEventIdsFetcher,
        private EventInfoGetByPlaceIdsFetcher $eventInfoGetByPlaceIdsFetcher,
        private UnionCategoryGetByIdsFetcher $categoryFetcher,
        private UnionCategorySerializer $categorySerializer,
        private UnionGetMemberedUnionIdsFetcher $unionGetMemberedUnionIdsFetcher,
        private UnionIsNotificationSubscribeFetcher $unionIsNotificationSubscribeFetcher,
        private UnionSphereGetByCategoryIdsFetcher $sphereFetcher,
        private UnionSphereGetByIdsFetcher $sphereGetByIdsFetcher,
        private CommunityGetSectionsFetcher $communityGetSectionsFetcher,
        private PlaceInfoGetByUnionIdsFetcher $placeInfoGetByUnionIdsFetcher,
        private UnionPlaceInfoSerializer $unionPlaceInfoSerializer,
        private EventInfoGetByUnionIdsFetcher $eventInfoGetByUnionIdsFetcher,
        private EventInfoGetByIdsFetcher $eventInfoGetByIdsFetcher,
        private UnionEventInfoSerializer $unionEventInfoSerializer,
        private Translator $translator
    ) {}

    public function unifyOne(?int $userId, ?array $item, array|string $fields = []): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : [], $fields);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items, array|string $fields = []): array
    {
        $fields = toArrayString($fields);

        $items = $this->unionSerializer->serializeItems($items, $fields);

        $entityIds = $this->getEntityIds($items);

        $items = $this->mapPlaceInfo($items, $this->getPlaceInfo($entityIds['placeIds']));
        $items = $this->mapEventByUnionInfo($items, $this->getEventByUnionInfo($entityIds['eventIds']));
        $items = $this->mapEventInfo($items, $this->getEventInfo($entityIds['eventInfoIds']));

        if (!empty($fields)) {
            if (\in_array('dates', $fields, true)) {
                $items = $this->mapDates($items, $this->getDates($entityIds['unionIds']));
            }

            if (\in_array('union', $fields, true)) {
                $items = $this->mapPlaces($items, $this->getPlaces($entityIds['eventIds']));
            }

            if (\in_array('country', $fields, true)) {
                $items = $this->mapCountries($items, $this->getCountries($entityIds['countryIds']));
            }

            if (\in_array('city', $fields, true)) {
                $items = $this->mapCities($items, $this->getCities($entityIds['cityIds']));
            }

            if (\in_array('category', $fields, true)) {
                if (\count($entityIds['eventIds']) > 0) {
                    $items = $this->mapEventCategories($items, $this->getEventSpheres($entityIds['categoryIds']));
                } else {
                    $items = $this->mapCategories($items, $this->getCategories($entityIds['categoryIds']));
                    $items = $this->mapSpheres($items, $this->getSpheres($entityIds['categoryIds']));
                }
            }

            if (\in_array('subcategory', $fields, true)) {
                $items = $this->mapSubcategories($items, $this->getSubcategories($entityIds['subcategoryIds']));
            }

            if (
                $userId !== null &&
                (
                    \in_array('member', $fields, true) ||
                    \in_array('permissions', $fields, true)
                )
            ) {
                $membered = $this->getMembered($userId, $entityIds['unionIds']);

                if (\in_array('member', $fields, true)) {
                    $items = $this->mapMembered($items, $membered);
                }

                if (\in_array('permissions', $fields, true)) {
                    $items = $this->mapPermissions($items, $membered, $items);
                }
            }

            if (\count($items) === 1 && \in_array('sections', $fields, true)) {
                /** @var array{array{id: int}} $items */
                foreach ($items as $k => $item) {
                    $items[$k]['sections'] = $this->communityGetSectionsFetcher->fetch(
                        new CommunityGetSectionsQuery(
                            unionId: $item['id']
                        )
                    );
                }
            }

            if ($userId !== null && \count($items) === 1 && \in_array('notification', $fields, true)) {
                /** @var array{array{id: int, notification: int}} $items */
                foreach ($items as $k => $item) {
                    $items[$k]['notification'] = $this->unionIsNotificationSubscribeFetcher->fetch(
                        new UnionIsNotificationSubscribeQuery(
                            userId: $userId,
                            unionId: $item['id']
                        )
                    ) ? 1 : 0;
                }
            }
        }

        return $items;
    }

    private function getPlaceInfo(array $ids): array
    {
        return $this->unionPlaceInfoSerializer->serializeItems(
            $this->placeInfoGetByUnionIdsFetcher->fetch(
                new PlaceInfoGetByUnionIdsQuery($ids)
            )
        );
    }

    private function getEventByUnionInfo(array $ids): array
    {
        return $this->unionEventInfoSerializer->serializeItems(
            $this->eventInfoGetByUnionIdsFetcher->fetch(
                new EventInfoGetByUnionIdsQuery($ids)
            )
        );
    }

    private function getEventInfo(array $ids): array
    {
        return $this->unionEventInfoSerializer->serializeItems(
            $this->eventInfoGetByIdsFetcher->fetch(
                new EventInfoGetByIdsQuery($ids)
            )
        );
    }

    private function getPlaces(array $ids): array
    {
        return $this->unionSerializer->serializeItems(
            $this->unionGetByEventIdsFetcher->fetch(
                new UnionGetByEventIdsQuery($ids)
            )
        );
    }

    private function getDates(array $ids): array
    {
        return $this->eventInfoGetByPlaceIdsFetcher->fetch(
            new EventInfoGetByPlaceIdsQuery($ids)
        );
    }

    private function getCountries(array $ids): array
    {
        return $this->countrySerializer->serializeItems(
            $this->countryFetcher->fetch(
                new DataCountryGetByIdsQuery($ids, $this->translator->getLocale())
            )
        );
    }

    private function getCities(array $ids): array
    {
        return $this->citySerializer->serializeItems(
            $this->cityFetcher->fetch(
                new DataCityGetByIdsQuery($ids, $this->translator->getLocale())
            )
        );
    }

    private function getSpheres(array $ids): array
    {
        return $this->sphereFetcher->fetch(
            new UnionSphereGetByCategoryIdsQuery(
                ids: $ids,
                locale: $this->translator->getLocale()
            )
        );
    }

    private function getEventSpheres(array $ids): array
    {
        return $this->sphereGetByIdsFetcher->fetch(
            new UnionSphereGetByIdsQuery(
                ids: $ids,
                locale: $this->translator->getLocale()
            )
        );
    }

    private function getCategories(array $ids): array
    {
        return $this->categorySerializer->serializeItems(
            $this->categoryFetcher->fetch(
                new UnionCategoryGetByIdsQuery(
                    ids: $ids,
                    locale: $this->translator->getLocale()
                )
            )
        );
    }

    private function getSubcategories(array $ids): array
    {
        return $this->categorySerializer->serializeItems(
            $this->categoryFetcher->fetch(
                new UnionCategoryGetByIdsQuery(
                    ids: $ids,
                    locale: $this->translator->getLocale()
                )
            )
        );
    }

    private function getMembered(?int $userId, array $ids): array
    {
        if (empty($userId)) {
            return [];
        }

        $query = new UnionGetMemberedUnionIdsQuery(
            userId: $userId,
            ids: $ids
        );

        return $this->unionGetMemberedUnionIdsFetcher->fetch($query);
    }

    private function mapPlaceInfo(array $items, array $places): array
    {
        /**
         * @var int $key
         * @var array{array{id:int|null}} $items
         */
        foreach ($items as $key => $item) {
            /** @var array{
             *     unionId: int,
             *     latitude: float|null,
             *     longitude: float|null,
             *     map: string|null,
             *     address: string|null,
             *     email: string|null,
             *     phone: string|null,
             *     phoneDescription: string|null,
             *     open: bool|null,
             *     workingHours: array|null,
             * } $place
             */
            foreach ($places as $place) {
                if ($item['id'] === $place['unionId']) {
                    $items[$key]['latitude'] = $place['latitude'];
                    $items[$key]['longitude'] = $place['longitude'];
                    $items[$key]['map'] = $place['map'];
                    $items[$key]['address'] = $place['address'];
                    $items[$key]['email'] = $place['email'];
                    $items[$key]['phone'] = $place['phone'];
                    $items[$key]['phoneDescription'] = $place['phoneDescription'];
                    $items[$key]['open'] = $place['open'];
                    $items[$key]['workingHours'] = $place['workingHours'];
                    break;
                }
            }
        }

        return $items;
    }

    private function mapEventByUnionInfo(array $items, array $events): array
    {
        /**
         * @var int $key
         * @var array{array{id:int|null}} $items
         */
        foreach ($items as $key => $item) {
            /** @var array{unionId: int, timeStart: int|null, timeEnd: int|null} $event */
            foreach ($events as $event) {
                if ($item['id'] === $event['unionId']) {
                    $items[$key]['timeStart'] = $event['timeStart'];
                    $items[$key]['timeEnd'] = $event['timeEnd'];
                    break;
                }
            }
        }

        return $items;
    }

    private function mapEventInfo(array $items, array $events): array
    {
        /**
         * @var int $key
         * @var array{event_id:int|null}[] $items
         */
        foreach ($items as $key => $item) {
            /** @var array{id: int, timeStart: int|null, timeEnd: int|null} $event */
            foreach ($events as $event) {
                if (!isset($item['event_id'])) {
                    continue;
                }

                if ($item['event_id'] === $event['id']) {
                    $items[$key]['timeStart'] = $event['timeStart'];
                    $items[$key]['timeEnd'] = $event['timeEnd'];
                    break;
                }
            }
        }

        return $items;
    }

    private function mapDates(array $items, array $events): array
    {
        /**
         * @var int $key
         * @var array{array{id:int|null}} $items
         */
        foreach ($items as $key => $item) {
            /** @var array{union_id: int, time_start: int|null, time_end: int|null} $event */
            foreach ($events as $event) {
                if ($item['id'] === $event['union_id']) {
                    $items[$key]['dates'][] = [
                        'timeStart' => $event['time_start'],
                        'timeEnd' => $event['time_end'],
                    ];
                }
            }
        }

        return $items;
    }

    private function mapPlaces(array $items, array $unions): array
    {
        $placeIds = [];

        /** @var array{id: int} $union */
        foreach ($unions as $union) {
            $placeIds[] = $union['id'];
        }

        $unions = $this->mapPlaceInfo($unions, $this->getPlaceInfo($placeIds));

        /** @var array{id:int|null}[] $items */
        foreach ($items as $key => $item) {
            $items[$key]['union'] = null;

            /** @var array{union_id:int} $union */
            foreach ($unions as $union) {
                if ($item['id'] === $union['union_id']) {
                    $items[$key]['union'] = $union;
                    unset($items[$key]['union']['union_id']);
                    break;
                }
            }
        }

        return $items;
    }

    private function mapCountries(array $items, array $countries): array
    {
        /**
         * @var int $key
         * @var array{array{countryId:int|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['country'] = null;

            if (null !== $item['countryId']) {
                /** @var array{id:int} $country */
                foreach ($countries as $country) {
                    if ($item['countryId'] === $country['id']) {
                        $items[$key]['country'] = $country;
                        break;
                    }
                }
            }

            if (isset($items[$key]['countryId'])) {
                unset($items[$key]['countryId']);
            }
        }

        return $items;
    }

    private function mapCities(array $items, array $cities): array
    {
        /**
         * @var int $key
         * @var array{cityId:int|null, workingHours: array{from: int, to: int, closed: bool}[]}[] $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['city'] = null;

            if (null !== $item['cityId']) {
                /** @var array{id:int, timezone:int} $city */
                foreach ($cities as $city) {
                    if ($item['cityId'] === $city['id']) {
                        $items[$key]['city'] = $city;
                        if (isset($item['workingHours'], $city['timezone'])) {
                            $items[$key]['open'] = $this->isOpen($item['workingHours'], $city['timezone']);
                        }
                        break;
                    }
                }
            }

            if (isset($items[$key]['cityId'])) {
                unset($items[$key]['cityId']);
            }
        }

        return $items;
    }

    /** @param array{from: int, to: int, closed: bool}[] $workingHours */
    private function isOpen(array $workingHours, int $timezone): bool
    {
        $time = time() + $timezone;
        $seconds = date('G', $time) * 3600 + (int)date('i', $time) * 60;

        $day = (int)date('N', $time) - 1;

        $working = $workingHours[$day] ?? null;

        if (null === $working) {
            return false;
        }

        if ($working['closed']) {
            return false;
        }

        if ($working['from'] === $working['to']) {
            return true;
        }

        if ($working['from'] <= $seconds && $seconds < $working['to']) {
            return true;
        }

        return false;
    }

    private function mapCategories(array $items, array $categories): array
    {
        /**
         * @var int $key
         * @var array{array{categoryId:int|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['category'] = null;

            if (null !== $item['categoryId']) {
                /** @var array{id:int} $category */
                foreach ($categories as $category) {
                    if ($item['categoryId'] === $category['id']) {
                        $items[$key]['category'] = $category;
                        break;
                    }
                }
            }

            if (isset($items[$key]['categoryId'])) {
                unset($items[$key]['categoryId']);
            }
        }

        return $items;
    }

    private function mapEventCategories(array $items, array $categories): array
    {
        /**
         * @var int $key
         * @var array{array{categoryId:int|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['category'] = null;

            if (null !== $item['categoryId']) {
                /** @var array{id:int} $category */
                foreach ($categories as $category) {
                    if ($item['categoryId'] === $category['id']) {
                        $items[$key]['category'] = $category;
                        $items[$key]['category']['sphere'] = null;
                        break;
                    }
                }
            }

            if (isset($items[$key]['categoryId'])) {
                unset($items[$key]['categoryId']);
            }
        }

        return $items;
    }

    private function mapSpheres(array $items, array $spheres): array
    {
        /**
         * @var int $key
         * @var array{category: array{id:int|null}}[] $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['category']['sphere'] = null;

            /** @var array{category_id:int} $sphere */
            foreach ($spheres as $sphere) {
                if (!isset($item['category'])) {
                    continue;
                }

                if ($item['category']['id'] === $sphere['category_id']) {
                    $items[$key]['category']['sphere'] = $this->categorySerializer->serialize(
                        $sphere
                    );
                    break;
                }
            }
        }

        return $items;
    }

    private function mapSubcategories(array $items, array $subcategories): array
    {
        /**
         * @var int $key
         * @var array{array{subcategoryId:int|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['subcategory'] = null;

            if (null !== $item['subcategoryId']) {
                /** @var array{id:int} $subcategory */
                foreach ($subcategories as $subcategory) {
                    if ($item['subcategoryId'] === $subcategory['id']) {
                        $items[$key]['subcategory'] = $subcategory;
                        break;
                    }
                }
            }

            if (isset($items[$key]['subcategoryId'])) {
                unset($items[$key]['subcategoryId']);
            }
        }

        return $items;
    }

    private function mapMembered(array $items, array $membered): array
    {
        /** @var array{array{id:int, member:int}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['member'] = 0;

            if (isset($membered[$item['id']])) {
                if ($membered[$item['id']] === Union::roleInvite()) {
                    $items[$key]['member'] = 1;
                } elseif ($membered[$item['id']] === Union::roleRequest()) {
                    $items[$key]['member'] = 2;
                } else {
                    $items[$key]['member'] = 3;
                }
            }
        }

        return $items;
    }

    private function mapPermissions(array $items, array $membered, array $unions): array
    {
        /** @var array{array{id:int, permissions:string[]}} $items */
        foreach ($items as $key => $item) {
            $permissions = [
                'audios:read',
                'members:read',
                'posts:read',
                'photos:read',
            ];

            /** @var array{id: int, services: string[]} $union */
            foreach ($unions as $union) {
                if ($union['id'] !== $item['id']) {
                    continue;
                }

                if (\in_array('audio', $union['services'], true)) {
                    $permissions = array_merge(
                        $permissions,
                        [
                            'audio-albums:read',
                            'audio-playlists:read',
                            'audios:read',
                        ]
                    );
                    break;
                }
            }

            if (
                isset($membered[$item['id']]) &&
                \in_array($membered[$item['id']], [Union::roleCreator(), Union::roleAdmin()], true)
            ) {
                $permissions = array_merge(
                    $permissions,
                    [
                        'posts-postponed:read',
                        'posts:create',
                        'posts-postponed:create',
                        'manage:read',
                        'manage:update',
                        'manage:photo',
                        'manage:cover',
                        'manage:section',
                    ]
                );
            }

            $items[$key]['permissions'] = $permissions;
        }

        return $items;
    }

    /** @return array{unionIds:int[],placeIds:int[],eventIds:int[],eventInfoIds:int[],countryIds:int[],cityIds:int[],categoryIds:int[],subcategoryIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $unionIds       = [];
        $placeIds       = [];
        $eventIds       = [];
        $eventInfoIds   = [];
        $countryIds     = [];
        $cityIds        = [];
        $categoryIds    = [];
        $subcategoryIds = [];

        /** @var array{id:int, event_id: int|null, type: int|null, countryId:int|null, cityId:int|null, categoryId:int|null, subcategoryId:int|null} $item */
        foreach ($items as $item) {
            $unionIds[] = $item['id'];

            if ($item['type'] === Union::typePlace()) {
                $placeIds[] = $item['id'];
            }

            if ($item['type'] === Union::typeEvent()) {
                $eventIds[] = $item['id'];

                if (isset($item['event_id'])) {
                    $eventInfoIds[] = $item['event_id'];
                }
            }

            if (!empty($item['countryId'])) {
                $countryIds[] = $item['countryId'];
            }

            if (!empty($item['cityId'])) {
                $cityIds[] = $item['cityId'];
            }

            if (!empty($item['categoryId'])) {
                $categoryIds[] = $item['categoryId'];
            }

            if (!empty($item['subcategoryId'])) {
                $subcategoryIds[] = $item['subcategoryId'];
            }
        }

        return [
            'unionIds'          => array_unique($unionIds),
            'placeIds'          => array_unique($placeIds),
            'eventIds'          => array_unique($eventIds),
            'eventInfoIds'      => array_unique($eventInfoIds),
            'countryIds'        => array_unique($countryIds),
            'cityIds'           => array_unique($cityIds),
            'categoryIds'       => array_unique($categoryIds),
            'subcategoryIds'    => array_unique($subcategoryIds),
        ];
    }
}
