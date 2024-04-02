<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

use App\Modules\Union\Entity\Union\Union;

use function ZayMedia\Shared\Components\Functions\toArrayString;

class UnionSerializer
{
    public function serialize(?array $union, array|string $fields = []): ?array
    {
        if (empty($union)) {
            return null;
        }

        $fields = toArrayString($fields);

        $services = [];

        if ($union['is_music']) {
            $services[] = 'audio';
        }

        $arr = [
            'id'            => $union['id'],
            'screenName'    => $union['screen_name'] ?? null,
            'type'          => $union['type'],
            'ageLimit'      => $union['age_limits'],
            'membersHide'   => (bool)$union['members_hide'],
            'services'      => $services,
            'name'          => $union['name'] ?? '',
            'status'        => $union['status'] ?? null,
            'description'   => $union['description'] ?? null,
            'website'       => $union['website'] ?? null,
            'photo'         => $this->getPhoto($union),
            'albumProfile'  => [
                'id'      => $union['album_profile_id'] ?? null,
                'photoId' => $union['photo_id'] ?? null,
            ],
            'cover'         => $this->getCover($union),
            'countryId'     => $union['country_id'] ?? null,
            'cityId'        => $union['city_id'] ?? null,
            'categoryId'    => $union['category_id'] ?? null,
            'subcategoryId' => $union['subcategory_id'] ?? null,
            'verified'      => (int)($union['verified'] ?? 0),
            'url'           => $this->getLinkUrl((int)$union['type'], (int)$union['id']),
            'link'          => $this->getLink((int)$union['type'], (int)$union['id']),
        ];

        if ($union['type'] === Union::typeCommunity()) {
            $arr['kind'] = (int)$union['access'];
        }

        if (\in_array('member', $fields, true)) {
            $arr['member'] = 0;
        }

        if (isset($union['union_id'])) {
            $arr['union_id'] = (int)$union['union_id'];
        }

        if (isset($union['event_id'])) {
            $arr['event_id'] = (int)$union['event_id'];
        }

        if (\in_array('counters', $fields, true)) {
            $arr['counters'] = [
                'members'       => $union['count_members'] ?? 0,
                'audios'        => $union['count_audios'] ?? 0,
                'audioAlbums'   => $union['count_audio_albums'] ?? 0,
                'audioSingles'  => $union['count_audio_singles'] ?? 0,
                'audioLikes'    => $union['count_audio_likes'] ?? 0,
                'photos'        => $union['count_photos'] ?? 0,
                'albums'        => $union['count_albums'] ?? 0,
                'posts'         => $union['count_posts'] ?? 0,
                'flows'         => $union['count_flows'] ?? 0,
                'flowViews'     => $union['count_flow_views'] ?? 0,
                'videos'        => $union['count_videos'] ?? 0,
                'links'         => $union['count_links'] ?? 0,
                'contacts'      => $union['count_contacts'] ?? 0,
                'events'        => $union['count_events'] ?? 0,
                'radios'        => $union['count_radios'] ?? 0,
                'stocks'        => $union['count_stocks'] ?? 0,
                'stickers'      => $union['count_stickers'] ?? 0,
            ];
        }

        return $arr;
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

    public function getLink(int $type, int $id): array
    {
        return [
            'app'   => $this->getAppUrl($type, $id),
            'link'  => $this->getLinkUrl($type, $id),
            'web'   => $this->getWebUrl($type, $id),
        ];
    }

    public function getAppUrl(int $type, int $id): string
    {
        return match ($type) {
            Union::typeEvent() => 'lo://e/' . $id,
            Union::typePlace() => 'lo://p/' . $id,
            default            => 'lo://c/' . $id
        };
    }

    public function getLinkUrl(int $type, int $id): string
    {
        return match ($type) {
            Union::typeEvent() => 'https://lo.ink/e/' . $id,
            Union::typePlace() => 'https://lo.ink/p/' . $id,
            default            => 'https://lo.ink/c/' . $id
        };
    }

    public function getWebUrl(int $type, int $id): string
    {
        return match ($type) {
            Union::typeEvent() => 'https://lo.ink/e/' . $id,
            Union::typePlace() => 'https://lo.ink/p/' . $id,
            default            => 'https://lo.ink/c/' . $id
        };
    }

    private function getPhoto(array $item): ?array
    {
        /** @var array{photo:string} $item */
        if (!empty($item['photo'])) {
            /** @var bool|string[]|null $sizes */
            $sizes = json_decode($item['photo'], true);

            if (!\is_array($sizes)) {
                return null;
            }

            /** @var bool|string[]|null $cropSizes */
            $cropSizes = $sizes['crop_square'] ?? [];

            if (!\is_array($cropSizes)) {
                return null;
            }

            ksort($cropSizes);
            $cropSizes = array_values($cropSizes);

            if (\count($cropSizes) > 0) {
                return [
                    'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                    'original'  => $sizes['original'] ?? null,
                ];
            }
        }

        return null;
    }

    private function getCover(array $item): ?array
    {
        /** @var array{cover:string} $item */
        if (!empty($item['cover'])) {
            /** @var bool|string[]|null $sizes */
            $sizes = json_decode($item['cover'], true);

            if (!\is_array($sizes)) {
                return null;
            }

            /** @var bool|string[]|null $cropSizes */
            $cropSizes = $sizes['crop_custom'] ?? [];

            if (!\is_array($cropSizes)) {
                return null;
            }

            ksort($cropSizes);
            $cropSizes = array_values($cropSizes);

            if (\count($cropSizes) > 0) {
                return [
                    'xs'        => $cropSizes[0] ?? $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'sm'        => $cropSizes[1] ?? $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'md'        => $cropSizes[2] ?? $cropSizes[3] ?? $sizes['original'] ?? null,
                    'lg'        => $cropSizes[3] ?? $cropSizes[2] ?? $sizes['original'] ?? null,
                    'original'  => $sizes['original'] ?? null,
                ];
            }
        }

        return null;
    }
}
