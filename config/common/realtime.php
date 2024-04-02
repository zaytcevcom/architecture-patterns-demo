<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ZayMedia\Shared\Components\Realtime\Centrifugo;
use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

return [
    Realtime::class => static function (ContainerInterface $container): Realtime {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     host:string,
         *     api-key:string,
         *     secret:string
         * } $config
         */
        $config = $container->get('config')['centrifugo'];

        return new Centrifugo(
            host: $config['host'],
            apiKey: $config['api-key'],
            secret: $config['secret']
        );
    },

    'config' => [
        'centrifugo' => [
            'host' => env('CENTRIFUGO_HOST'),
            'api-key' => env('CENTRIFUGO_API_KEY'),
            'secret' => env('CENTRIFUGO_SECRET'),
        ],
    ],
];
