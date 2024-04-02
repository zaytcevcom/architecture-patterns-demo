<?php

declare(strict_types=1);

namespace App\Modules\Post\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class PostRealtimeNotifier
{
    private const PREFIX = 'post-';

    private const TYPE_UPDATE           = 0;
    private const TYPE_DELETE           = 1;
    private const TYPE_RESTORE          = 2;
    private const TYPE_NEW_COMMENT      = 3;
    private const TYPE_UPDATE_COMMENT   = 4;
    private const TYPE_DELETE_COMMENT   = 5;
    private const TYPE_RESTORE_COMMENT  = 6;

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $postId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $postId;
    }

    public function update(int $postId, array $data): void
    {
        $this->publish(self::TYPE_UPDATE, $postId, $data);
    }

    public function delete(int $postId): void
    {
        $this->publish(self::TYPE_DELETE, $postId, null);
    }

    public function restore(int $postId, array $data): void
    {
        $this->publish(self::TYPE_RESTORE, $postId, $data);
    }

    public function newComment(int $postId, array $data): void
    {
        $this->publish(self::TYPE_NEW_COMMENT, $postId, $data);
    }

    public function updateComment(int $postId, array $data): void
    {
        $this->publish(self::TYPE_UPDATE_COMMENT, $postId, $data);
    }

    public function deleteComment(int $postId, array $data): void
    {
        $this->publish(self::TYPE_DELETE_COMMENT, $postId, $data);
    }

    public function restoreComment(int $postId, array $data): void
    {
        $this->publish(self::TYPE_RESTORE_COMMENT, $postId, $data);
    }

    private function publish(int $type, int $postId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($postId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}
