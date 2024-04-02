<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\User;

use App\Modules\Contact\Query\Blacklist\GetBlacklistedByMe\ContactGetBlacklistedByMeFetcher;
use App\Modules\Contact\Query\Blacklist\GetBlacklistedByMe\ContactGetBlacklistedByMeQuery;
use App\Modules\Contact\Query\Blacklist\GetBlacklistedMe\ContactGetBlacklistedMeFetcher;
use App\Modules\Contact\Query\Blacklist\GetBlacklistedMe\ContactGetBlacklistedMeQuery;
use App\Modules\Contact\Query\GetRelationship\ContactGetRelationshipFetcher;
use App\Modules\Contact\Query\GetRelationship\ContactGetRelationshipQuery;
use App\Modules\Contact\Query\Notification\IsNotificationSubscribe\ContactIsNotificationSubscribeFetcher;
use App\Modules\Contact\Query\Notification\IsNotificationSubscribe\ContactIsNotificationSubscribeQuery;
use App\Modules\Data\Query\City\GetByIds\DataCityGetByIdsFetcherCached;
use App\Modules\Data\Query\City\GetByIds\DataCityGetByIdsQuery;
use App\Modules\Data\Query\Country\GetByIds\DataCountryGetByIdsFetcherCached;
use App\Modules\Data\Query\Country\GetByIds\DataCountryGetByIdsQuery;
use App\Modules\Data\Service\CitySerializer;
use App\Modules\Data\Service\CountrySerializer;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsFetcherCached;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsQuery;
use App\Modules\Identity\Service\UserSerializer;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

use function ZayMedia\Shared\Components\Functions\toArrayString;

final class UserUnifier implements UnifierInterface
{
    private ?array $countries = null;
    private ?array $cities = null;
    private ?array $users = null;

    public function __construct(
        private readonly IdentityGetByIdsFetcherCached $userFetcher,
        private readonly UserSerializer $userSerializer,
        private readonly DataCountryGetByIdsFetcherCached $countryFetcher,
        private readonly CountrySerializer $countrySerializer,
        private readonly DataCityGetByIdsFetcherCached $cityFetcher,
        private readonly CitySerializer $citySerializer,
        private readonly ContactIsNotificationSubscribeFetcher $contactIsNotificationSubscribeFetcher,
        private readonly ContactGetRelationshipFetcher $contactGetRelationshipFetcher,
        private readonly ContactGetBlacklistedByMeFetcher $contactGetBlacklistedByMeFetcher,
        private readonly ContactGetBlacklistedMeFetcher $contactGetBlacklistedMeFetcher,
        private readonly Translator $translator
    ) {}

    public function setPrivateFieldsForCore(array $fields): void
    {
        $this->userSerializer->setPrivateFieldsForCore($fields);
    }

    public function unifyOne(?int $userId, ?array $item, array|string $fields = []): array
    {
        $this->resetStorage();

        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : [], $fields);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items, array|string $fields = []): array
    {
        $this->resetStorage();

        $fields = toArrayString($fields);
        $items = $this->userSerializer->serializeItems($items, $fields);

        if (!empty($fields)) {
            $entityIds = $this->getEntityIds($items);

            if (\in_array('country', $fields, true)) {
                $items = $this->mapCountries($items, $this->getCountries($entityIds['countryIds']));
            }

            if (\in_array('city', $fields, true)) {
                $items = $this->mapCities($items, $this->getCities($entityIds['cityIds']));
            }

            if (\in_array('contacts', $fields, true)) {
                $items = $this->mapContactCountries($items, $this->getCountries($entityIds['countryIds']));
                $items = $this->mapContactCities($items, $this->getCities($entityIds['cityIds']));
            }

            if (\in_array('marital', $fields, true)) {
                $items = $this->mapMarital($items, $this->getUsers($entityIds['maritalIds']));
            }

            if (null !== $userId && \in_array('blacklisted', $fields, true)) {
                $items = $this->mapBlacklisted($items, $this->getBlacklisted($userId, $entityIds['userIds']));
            }

            if (null !== $userId && \in_array('blacklistedByMe', $fields, true)) {
                $items = $this->mapBlacklistedByMe($items, $this->getBlacklistedByMe($userId, $entityIds['userIds']));
            }

            if (\count($items) === 1 && \in_array('relationship', $fields, true)) {
                $items = $this->mapRelationship($userId, $items);
            }
        }

        return $items;
    }

    public function unifyGroupedByLastName(?int $userId, array $items, array|string $fields = []): array
    {
        $items = $this->unify($userId, $items, $fields);

        $result = [];

        /** @var array{array{lastName:string}} $items */
        foreach ($items as $item) {
            $value = mb_substr($item['lastName'], 0, 1, 'utf8');

            $key = array_search($value, array_column($result, 'value'), true);

            if ($key === false) {
                $result[] = [
                    'value' => $value,
                    'items' => [],
                ];

                $key = \count($result) - 1;
            }

            $result[$key]['items'][] = $item;
        }

        return $result;
    }

    public function unifyGroupedByDay(?int $userId, array $items, array|string $fields = []): array
    {
        $items = $this->unify($userId, $items, $fields);

        $result = [];

        /** @var array{array{birthday:array{date:int|null}}} $items */
        foreach ($items as $item) {
            if (isset($item['birthday']['date']) && !empty($item['birthday']['date'])) {
                $value = strtotime(date('Y') . '-' . date('m-d', $item['birthday']['date']));
            } else {
                $value = null;
            }

            $key = array_search($value, array_column($result, 'value'), true);

            if ($key === false) {
                $result[] = [
                    'value' => $value,
                    'items' => [],
                ];

                $key = \count($result) - 1;
            }

            $result[$key]['items'][] = $item;
        }

        return $result;
    }

    public function unifyWithMutual(?int $userId, array $items, array $mutual, array|string $fields = []): array
    {
        $items = $this->unify($userId, $items, $fields);

        /**
         * @var int $key
         * @var array{array{id:int}} $items
         */
        foreach ($items as $key => $item) {
            /** @var array $arr */
            $arr = (isset($mutual[$item['id']])) ? $mutual[$item['id']] : [];
            $arrShortened = [];

            /** @var array $z */
            foreach ($arr as $z) {
                $arrShortened[] = $z;

                if (\count($arrShortened) === 3) {
                    break;
                }
            }

            $items[$key]['mutual'] = [
                'count' => \count($arr),
                'items' => $this->userSerializer->serializeItems($arrShortened),
            ];
        }

        return $items;
    }

    private function resetStorage(): void
    {
        $this->countries = null;
        $this->cities = null;
        $this->users = null;
    }

    private function getCountries(array $ids): array
    {
        if (null !== $this->countries) {
            return $this->countries;
        }

        $this->countries = $this->countrySerializer->serializeItems(
            $this->countryFetcher->fetch(
                new DataCountryGetByIdsQuery($ids, $this->translator->getLocale())
            )
        );

        return $this->countries;
    }

    private function getCities(array $ids): array
    {
        if (null !== $this->cities) {
            return $this->cities;
        }

        $this->cities = $this->citySerializer->serializeItems(
            $this->cityFetcher->fetch(
                new DataCityGetByIdsQuery($ids, $this->translator->getLocale())
            )
        );

        return $this->cities;
    }

    private function getUsers(array $ids): array
    {
        if (null !== $this->users) {
            return $this->users;
        }

        $this->users = $this->userSerializer->serializeItems(
            $this->userFetcher->fetch(
                new IdentityGetByIdsQuery($ids)
            )
        );

        return $this->users;
    }

    private function getBlacklisted(int $userId, array $ids): array
    {
        $query = new ContactGetBlacklistedMeQuery(
            userId: $userId,
            ids: $ids
        );

        return $this->contactGetBlacklistedMeFetcher->fetch($query);
    }

    private function getBlacklistedByMe(int $userId, array $ids): array
    {
        $query = new ContactGetBlacklistedByMeQuery(
            userId: $userId,
            ids: $ids
        );

        return $this->contactGetBlacklistedByMeFetcher->fetch($query);
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
         * @var array{array{cityId:int|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['city'] = null;

            if (null !== $item['cityId']) {
                /** @var array{id:int} $city */
                foreach ($cities as $city) {
                    if ($item['cityId'] === $city['id']) {
                        $items[$key]['city'] = $city;
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

    private function mapMarital(array $items, array $users): array
    {
        /**
         * @var int $key
         * @var array{array{marital:array{userId:int|null}}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['marital']['user'] = null;

            if (null !== $item['marital']['userId']) {
                /** @var array{id:int} $user */
                foreach ($users as $user) {
                    if ($item['marital']['userId'] === $user['id']) {
                        $items[$key]['marital']['user'] = $user;
                        break;
                    }
                }
            }

            if (isset($items[$key]['marital']['userId'])) {
                unset($items[$key]['marital']['userId']);
            }
        }

        return $items;
    }

    private function mapBlacklisted(array $items, array $blacklisted): array
    {
        /** @var array{array{id:int}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['blacklisted'] = \in_array($item['id'], $blacklisted, true);
        }

        return $items;
    }

    private function mapBlacklistedByMe(array $items, array $blacklistedByMe): array
    {
        /** @var array{array{id:int}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['blacklistedByMe'] = \in_array($item['id'], $blacklistedByMe, true);
        }

        return $items;
    }

    private function mapContactCountries(array $items, array $countries): array
    {
        /**
         * @var int $key
         * @var array{array{contacts:array{countryId:int|null}}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['contacts']['country'] = null;

            if (null !== $item['contacts']['countryId']) {
                /** @var array{id:int} $country */
                foreach ($countries as $country) {
                    if ($item['contacts']['countryId'] === $country['id']) {
                        $items[$key]['contacts']['country'] = $country;
                        break;
                    }
                }
            }

            if (isset($items[$key]['contacts']['countryId'])) {
                unset($items[$key]['contacts']['countryId']);
            }
        }

        return $items;
    }

    private function mapContactCities(array $items, array $cities): array
    {
        /**
         * @var int $key
         * @var array{array{contacts:array{cityId:int|null}}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['contacts']['city'] = null;

            if (null !== $item['contacts']['cityId']) {
                /** @var array{id:int} $city */
                foreach ($cities as $city) {
                    if ($item['contacts']['cityId'] === $city['id']) {
                        $items[$key]['contacts']['city'] = $city;
                        break;
                    }
                }
            }

            if (isset($items[$key]['contacts']['cityId'])) {
                unset($items[$key]['contacts']['cityId']);
            }
        }

        return $items;
    }

    private function mapRelationship(?int $userId, array $items): array
    {
        /**
         * @var int $key
         * @var array{array{id:int,relationship:array}} $items
         */
        foreach ($items as $key => $item) {
            if (null === $userId) {
                $items[$key]['relationship'] = null;
                continue;
            }

            $queryNotificationSubscribe = new ContactIsNotificationSubscribeQuery(
                userId: $userId,
                contactId: $item['id']
            );

            $queryRelationship = new ContactGetRelationshipQuery(
                sourceId: $userId,
                targetId: $item['id']
            );

            $items[$key]['relationship'] = [
                'status'        => $this->contactGetRelationshipFetcher->fetch($queryRelationship),
                'notification'  => $this->contactIsNotificationSubscribeFetcher->fetch($queryNotificationSubscribe) ? 1 : 0,
            ];
        }

        return $items;
    }

    /** @return array{userIds:int[],countryIds:int[],cityIds:int[],maritalIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $userIds    = [];
        $countryIds = [];
        $cityIds    = [];
        $maritalIds = [];

        /** @var array{id:int, countryId:int,cityId:int,contacts:array{countryId:int,cityId:int},marital:array{userId:int}} $item */
        foreach ($items as $item) {
            $userIds[] = $item['id'];

            if (isset($item['countryId']) && !empty($item['countryId'])) {
                $countryIds[] = $item['countryId'];
            }

            if (isset($item['contacts']['countryId']) && !empty($item['contacts']['countryId'])) {
                $countryIds[] = $item['contacts']['countryId'];
            }

            if (isset($item['cityId']) && !empty($item['cityId'])) {
                $cityIds[] = $item['cityId'];
            }

            if (isset($item['contacts']['cityId']) && !empty($item['contacts']['cityId'])) {
                $cityIds[] = $item['contacts']['cityId'];
            }

            if (isset($item['marital']['userId']) && !empty($item['marital']['userId'])) {
                $maritalIds[] = $item['marital']['userId'];
            }
        }

        return [
            'userIds'       => array_unique($userIds),
            'countryIds'    => array_unique($countryIds),
            'cityIds'       => array_unique($cityIds),
            'maritalIds'    => array_unique($maritalIds),
        ];
    }
}
