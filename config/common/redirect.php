<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ZayMedia\Shared\Http\Middleware\HttpNotFoundRedirectExceptionHandler;

use function App\Components\env;

return [
    HttpNotFoundRedirectExceptionHandler::class => static function (ContainerInterface $container): HttpNotFoundRedirectExceptionHandler {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     location:string
         * } $config
         */
        $config = $container->get('config')['redirect'];

        return new HttpNotFoundRedirectExceptionHandler(
            location: $config['location']
        );
    },

    'config' => [
        'redirect' => [
            'location' => env('SCHEME') . '://' . env('DOMAIN_REDIRECT'),
        ],
    ],
];
