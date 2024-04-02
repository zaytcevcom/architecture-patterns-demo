<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ZayMedia\Shared\Components\Frontend\FrontendUrlGenerator;

use function App\Components\env;

return [
    FrontendUrlGenerator::class => static function (ContainerInterface $container): FrontendUrlGenerator {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{url:string} $config
         */
        $config = $container->get('config')['frontend'];

        return new FrontendUrlGenerator($config['url']);
    },

    'config' => [
        'frontend' => [
            'url' => env('SCHEME') . '://' . env('DOMAIN'),
        ],
    ],
];
