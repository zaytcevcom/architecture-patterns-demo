<?php

declare(strict_types=1);

namespace App\Modules\Post\Service;

class PostCommentSerializer
{
    public function serialize(array $postComment): ?array
    {
        if (empty($postComment)) {
            return null;
        }

        $comments = null;

        if (null === $postComment['comment_id']) {
            $comments = [
                'count'         => $postComment['count_comments'] ?? 0,
                'canView'       => null,
                'canComment'    => null,
                'items'         => [],
            ];
        }

        return [
            'id'            => $postComment['id'],
            'postId'        => $postComment['post_id'],
            'commentId'     => $postComment['comment_id'] ?? null,
            'userId'        => $postComment['user_id'],
            'unionId'       => $postComment['union_id'] ?? null,
            'message'       => $postComment['message'] ?? null,
            'time'          => $postComment['created_at'] ?? 0,
            'photoIds'      => $this->getPhotoIds($postComment),
            'audioIds'      => $this->getAudioIds($postComment),
            'videoIds'      => $this->getVideoIds($postComment),
            'likes' => [
                'count'     => $postComment['count_likes'] ?? 0,
                'canView'   => null,
                'isLiked'   => null,
            ],
            'comments' => $comments,
            'canEdit'   => null,
            'canDelete' => null,
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
}
