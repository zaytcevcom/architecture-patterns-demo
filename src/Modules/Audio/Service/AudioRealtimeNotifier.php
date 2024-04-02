<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class AudioRealtimeNotifier
{
    private const PREFIX = 'audio-';

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $audioId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $audioId;
    }

    public function update(int $audioId, array $data): void
    {
        $this->publish(0, $audioId, $data);
    }

    public function delete(int $audioId): void
    {
        $this->publish(1, $audioId, null);
    }

    public function restore(int $audioId, array $data): void
    {
        $this->publish(2, $audioId, $data);
    }

    private function publish(int $type, int $audioId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($audioId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}
