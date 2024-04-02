<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class AudioPlaylistRealtimeNotifier
{
    private const PREFIX = 'audioPlaylist-';

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $audioPlaylistId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $audioPlaylistId;
    }

    public function update(int $audioPlaylistId, array $data): void
    {
        $this->publish(0, $audioPlaylistId, $data);
    }

    public function delete(int $audioPlaylistId): void
    {
        $this->publish(1, $audioPlaylistId, null);
    }

    public function restore(int $audioPlaylistId, array $data): void
    {
        $this->publish(2, $audioPlaylistId, $data);
    }

    private function publish(int $type, int $audioPlaylistId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($audioPlaylistId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}
