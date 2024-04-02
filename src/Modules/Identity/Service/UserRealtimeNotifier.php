<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class UserRealtimeNotifier
{
    private const PREFIX = 'user-';

    private const TYPE_UPDATE           = 0;
    private const TYPE_DELETE           = 1;
    private const TYPE_RESTORE          = 2;

    private const TYPE_NEW_POST         = 3;
    private const TYPE_UPDATE_POST      = 4;
    private const TYPE_DELETE_POST      = 5;
    private const TYPE_RESTORE_POST     = 6;

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $userId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $userId;
    }

    public function update(int $userId, array $data): void
    {
        $this->publish(self::TYPE_UPDATE, $userId, $data);
    }

    public function delete(int $userId): void
    {
        $this->publish(self::TYPE_DELETE, $userId, null);
    }

    public function restore(int $userId, array $data): void
    {
        $this->publish(self::TYPE_RESTORE, $userId, $data);
    }

    public function newPost(int $userId, array $data): void
    {
        $this->publish(self::TYPE_NEW_POST, $userId, $data);
    }

    public function updatePost(int $userId, array $data): void
    {
        $this->publish(self::TYPE_UPDATE_POST, $userId, $data);
    }

    public function deletePost(int $userId, array $data): void
    {
        $this->publish(self::TYPE_DELETE_POST, $userId, $data);
    }

    public function restorePost(int $userId, array $data): void
    {
        $this->publish(self::TYPE_RESTORE_POST, $userId, $data);
    }

    private function publish(int $type, int $userId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($userId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}
