<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class UnionRealtimeNotifier
{
    private const PREFIX = 'union-';

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $unionId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $unionId;
    }

    public function update(int $unionId, array $data): void
    {
        $this->publish(0, $unionId, $data);
    }

    public function delete(int $unionId): void
    {
        $this->publish(1, $unionId, null);
    }

    public function restore(int $unionId, array $data): void
    {
        $this->publish(2, $unionId, $data);
    }

    public function newPost(int $unionId, array $data): void
    {
        $this->publish(3, $unionId, $data);
    }

    private function publish(int $type, int $unionId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($unionId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}
