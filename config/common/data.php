<?php

declare(strict_types=1);

use App\Components\Dadata;
use App\Components\Data;
use Dadata\DadataClient;
use Psr\Container\ContainerInterface;

use function App\Components\env;

return [
    Data::class => static function (ContainerInterface $container): Data {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{token:string, secret:string} $config
         */
        $config = $container->get('config')['data'];

        return new Dadata(
            new DadataClient(
                token: $config['token'],
                secret: $config['secret'],
            )
        );
    },

    'config' => [
        'data' => [
            'token' => env('DADATA_TOKEN'),
            'secret' => env('DADATA_SECRET'),
        ],
    ],
];
