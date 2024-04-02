<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

use function App\Components\env;

return [
    MailerInterface::class => static function (ContainerInterface $container): MailerInterface {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     host:string,
         *     port:int,
         *     encryption:string,
         *     user:string,
         *     password:string
         * } $config
         */
        $config = $container->get('config')['mailer'];

        $transport = (new EsmtpTransport(
            $config['host'],
            $config['port'],
            $config['encryption'] === 'tls',
            null,
            $container->get(LoggerInterface::class)
        ))
            ->setUsername($config['user'])
            ->setPassword($config['password']);

        return new Mailer($transport);
    },

    'config' => [
        'mailer' => [
            'host' => env('MAILER_HOST'),
            'port' => (int)env('MAILER_PORT'),
            'encryption' => env('MAILER_ENCRYPTION'),
            'user' => env('MAILER_USERNAME'),
            'password' => env('MAILER_PASSWORD'),
        ],
    ],
];
