<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use App\Modules\Identity\Entity\User\Fields\Verified;
use App\Modules\Identity\Entity\User\User;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\Transliterator\Transliterator;

use function ZayMedia\Shared\Components\Functions\toArrayString;

class UserSerializer
{
    private array $privateFieldsForCore = [];

    public function __construct(
        private readonly Translator $translator,
        private readonly Transliterator $transliterator
    ) {}

    public function setPrivateFieldsForCore(array $fields): void
    {
        $this->privateFieldsForCore = $fields;
    }

    public function serialize(array $user, array|string $fields = []): array
    {
        $fields = toArrayString($fields);

        $arr = [
            'id'            => $user['id'],
            'screenName'    => $user['screen_name'] ?? null,
            'firstName'     => $this->getFirstName($user),
            'lastName'      => $this->getLastName($user),
            'verified'      => (int)($user['verified'] ?? Verified::notVerified()->getValue()),
            'deactivated'   => (int)($user['deactivated'] ?? 0),
            'sex'           => $user['sex'] ?? null,
            'photo'         => $this->getPhoto($user),
            'albumProfile'  => [
                'id'      => $user['album_profile_id'] ?? null,
                'photoId' => $user['photo_id'] ?? null,
            ],
            'isOnline'      => $this->isOnline($user),
            'lastVisit'     => (int)($user['last_visit'] ?? 0),
            'rate' => [
                'total' => (int)($user['rate'] ?? 0) + (int)($user['rate_info'] ?? 0),
                'info'  => (int)($user['rate_info'] ?? 0),
            ],
            'countryId'     => $user['country_id'],
            'cityId'        => $user['city_id'],
            'birthday'      => $this->getBirthday($user),
            'status'        => $user['status'] ?? null,
            'url'           => User::getWebUrl((int)$user['id']),
            'link'          => $this->getLink((int)$user['id']),
        ];

        if (\in_array('contacts', $fields, true)) {
            $arr['contacts'] = [
                'countryId' => $user['contacts_country_id'],
                'cityId'    => $user['contacts_city_id'],
                'phone'     => $user['contacts_phone'] ?? null,
                'email'     => $user['contacts_email'] ?? null,
                'site'      => $user['contacts_site'] ?? null,
            ];
        }

        if (\in_array('interests', $fields, true)) {
            $arr['interests'] = [
                'activities'    => $user['interests_activities'] ?? '',
                'interests'     => $user['interests_interests'] ?? '',
                'music'         => $user['interests_music'] ?? '',
                'films'         => $user['interests_films'] ?? '',
                'tv'            => $user['interests_tv'] ?? '',
                'books'         => $user['interests_books'] ?? '',
                'citations'     => $user['interests_citations'] ?? '',
                'about'         => $user['interests_about'] ?? '',
            ];
        }

        if (\in_array('position', $fields, true)) {
            $arr['position'] = [
                'political'     => $user['position_political'] ?? '',
                'religion'      => $user['position_religion'] ?? '',
                'life'          => $user['position_life'] ?? '',
                'people'        => $user['position_people'] ?? '',
                'smoking'       => $user['position_smoking'] ?? '',
                'alcohol'       => $user['position_alcohol'] ?? '',
                'inspiredBy'    => $user['position_inspired_by'] ?? '',
            ];
        }

        if (\in_array('counters', $fields, true)) {
            $arr['counters'] = [
                'contacts'      => $user['count_contacts'] ?? 0,
                'subscribers'   => $user['count_subscribers'] ?? 0,
                'audios'        => $user['count_audios'] ?? 0,
                'photos'        => $user['count_photos'] ?? 0,
                'albums'        => $user['count_albums'] ?? 0,
                'posts'         => $user['count_posts'] ?? 0,
                'flows'         => $user['count_flows'] ?? 0,
                'flowViews'     => $user['count_flow_views'] ?? 0,
                'videos'        => $user['count_videos'] ?? 0,
                'communities'   => $user['count_communities'] ?? 0,
                'places'        => $user['count_places'] ?? 0,
                'events'        => $user['count_events'] ?? 0,
                'gifts'         => $user['count_gifts'] ?? 0,
                'stocks'        => $user['count_stocks'] ?? 0,
            ];
        }

        if (\in_array('marital', $fields, true)) {
            $arr['marital'] = [
                'status'    => $user['marital'],
                'userId'    => $user['marital_id'] ?? null,
            ];
        }

        return $this->addPrivateFieldsForCore($user, $arr);
    }

    public function serializeItems(array $items, array|string $fields = []): array
    {
        $result = [];

        /** @var array $item */
        foreach ($items as $item) {
            $result[] = $this->serialize($item, $fields);
        }

        return $result;
    }

    public function getLink(int $id): array
    {
        return [
            'app'   => User::getAppUrl($id),
            'web'   => User::getWebUrl($id),
        ];
    }

    public function getPhoto(array $item): ?array
    {
        /** @var array{photo:string} $item */
        if (isset($item['photo']) && !empty($item['photo'])) {
            return User::getPhotoParsed($item['photo']);
        }

        return null;
    }

    private function getFirstName(array $item): string
    {
        /** @var array{first_name: string|null, first_name_translit: string|null} $item */
        if (!$this->transliterator->isCyrillicLocale($this->translator->getLocale())) {
            return $item['first_name_translit'] ?? $item['first_name'] ?? '';
        }

        return $item['first_name'] ?? '';
    }

    private function getLastName(array $item): string
    {
        /** @var array{last_name: string|null, last_name_translit: string|null} $item */
        if (!$this->transliterator->isCyrillicLocale($this->translator->getLocale())) {
            return $item['last_name_translit'] ?? $item['last_name'] ?? '';
        }

        return $item['last_name'] ?? '';
    }

    private function isOnline(array $item): int
    {
        return (time() - (int)($item['last_visit'] ?? 0) < User::getDelayOnline()) ? 1 : 0;
    }

    private function getBirthday(array $item): ?array
    {
        $birthday = null;

        /** @var array{birthday: string} $item */
        if (isset($item['birthday']) && strtotime($item['birthday']) && !empty($item['birthday']) && $item['birthday'] !== '0000-00-00') {
            $birthday = [
                'date'  => (int)strtotime($item['birthday']),
                'year'  => (int)date('Y', strtotime($item['birthday'])),
                'month' => (int)date('m', strtotime($item['birthday'])),
                'day'   => (int)date('d', strtotime($item['birthday'])),
            ];
        }

        return $birthday;
    }

    private function addPrivateFieldsForCore(array $user, array $arr): array
    {
        if (\in_array('time_reg', $this->privateFieldsForCore, true)) {
            $arr['timeReg'] = (int)$user['time_reg'];
        }

        return $arr;
    }
}
