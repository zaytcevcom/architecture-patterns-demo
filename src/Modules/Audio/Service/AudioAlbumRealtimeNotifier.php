<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class AudioAlbumRealtimeNotifier
{
    private const PREFIX = 'audioAlbum-';

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $audioAlbumId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $audioAlbumId;
    }

    public function update(int $audioAlbumId, array $data): void
    {
        $this->publish(0, $audioAlbumId, $data);
    }

    public function delete(int $audioAlbumId): void
    {
        $this->publish(1, $audioAlbumId, null);
    }

    public function restore(int $audioAlbumId, array $data): void
    {
        $this->publish(2, $audioAlbumId, $data);
    }

    private function publish(int $type, int $audioAlbumId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($audioAlbumId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}
