<?php

declare(strict_types=1);

namespace App\Modules\Post\Service;

use App\Modules\Post\Entity\Post\Post;

class PostSerializer
{
    public function serialize(array $post): ?array
    {
        if (empty($post)) {
            return null;
        }

        /** @var string|null $message */
        $message = $post['message'] ?? null;

        if (!empty($message)) {
            $message = str_replace('\r\n', PHP_EOL, $message);
            $message = str_replace('\n', PHP_EOL, $message);
        }

        return [
            'id'            => $post['id'],
            'userId'        => $post['user_id'],
            'unionId'       => ($post['owner_id'] < 0) ? -1 * (int)$post['owner_id'] : null,
            'postId'        => $post['post_id'],
            'flowId'        => $post['flow_id'],
            'message'       => $message,
            'time'          => $post['date'] ?? 0,
            'photoIds'      => $this->getPhotoIds($post),
            'audioIds'      => $this->getAudioIds($post),
            'videoIds'      => $this->getVideoIds($post),
            'closeComments' => (bool)($post['close_comments'] ?? true),
            'contactsOnly'  => (bool)($post['contacts_only'] ?? false),
            'isPinned'      => (bool)($post['is_pinned'] ?? false),
            'likes' => [
                'count'     => $post['count_likes'] ?? 0,
                'canView'   => null,
                'isLiked'   => null,
            ],
            'comments' => [
                'count'         => $post['count_comments'] ?? 0,
                'canView'       => null,
                'canComment'    => null,
                'isCommented'   => null,
            ],
            'reposts' => [
                'count'         => $post['count_reposts'] ?? 0,
                'canView'       => null,
                'canRepost'     => null,
                'isReposted'    => null,
            ],
            'views' => [
                'count' => (int)($post['count_views'] ?? 0) + (int)($post['count_views_cheat'] ?? 0),
            ],
            'canEdit'   => null,
            'canManage' => null,
            'canDelete' => null,
            'url'       => Post::getWebUrl((int)$post['owner_id'], (int)$post['id']),
            'link'      => $this->getLink($post),
        ];
    }

    public function serializeItems(array $items): array
    {
        $result = [];

        /** @var array $item */
        foreach ($items as $item) {
            $result[] = $this->serialize($item);
        }

        return $result;
    }

    private function getPhotoIds(array $item): array
    {
        /** @var array{photo_ids: string|null} $item */
        $items = explode(',', $item['photo_ids'] ?? '');

        $result = [];

        foreach ($items as $item) {
            $item = (int)$item;

            if (!empty($item)) {
                $result[] = $item;
            }
        }

        return array_unique($result);
    }

    private function getAudioIds(array $item): array
    {
        /** @var array{audio_ids: string|null} $item */
        $items = explode(',', $item['audio_ids'] ?? '');

        $result = [];

        foreach ($items as $item) {
            $item = (int)$item;

            if (!empty($item)) {
                $result[] = $item;
            }
        }

        return array_unique($result);
    }

    private function getVideoIds(array $item): array
    {
        /** @var array{video_ids: string|null} $item */
        $items = explode(',', $item['video_ids'] ?? '');

        $result = [];

        foreach ($items as $item) {
            $item = (int)$item;

            if (!empty($item)) {
                $result[] = $item;
            }
        }

        return array_unique($result);
    }

    private function getLink(array $item): array
    {
        /** @var array{id:int, owner_id:int} $item */
        return [
            'app'   => Post::getAppUrl($item['id']),
            'web'   => Post::getWebUrl($item['owner_id'], $item['id']),
        ];
    }
}
