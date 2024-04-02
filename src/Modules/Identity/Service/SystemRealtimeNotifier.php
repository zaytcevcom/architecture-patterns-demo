<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class SystemRealtimeNotifier
{
    private const PREFIX = 'system-app';

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX;
    }

    public function updateExcludeSections(array $excludeSections = []): void
    {
        $this->realtime->publish(
            channel: self::getChannelName(),
            data: [
                'type' => 1,
                'payload' => [
                    'sections' => [
                        'exclude' => $excludeSections,
                    ],
                ],
            ]
        );
    }
}
