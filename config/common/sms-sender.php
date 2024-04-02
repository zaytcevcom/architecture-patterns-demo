<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ZayMedia\Shared\Components\SmsSender\SmsCRu;
use ZayMedia\Shared\Components\SmsSender\SmsPilot;
use ZayMedia\Shared\Components\SmsSender\SmsSender;

use function App\Components\env;

return [
    SmsSender::class => static function (ContainerInterface $container): SmsSender {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     login:string,
         *     password:string,
         *     sender:string
         * } $config
         */
        $config = $container->get('config')['sms-sender-smsc'];

        return new SmsCRu(
            login: $config['login'],
            password: $config['password'],
            sender: $config['sender']
        );
    },

    'config' => [
        'sms-sender-smsc' => [
            'login' => env('SMS_SENDER_SMSC_LOGIN'),
            'password' => env('SMS_SENDER_SMSC_PASSWORD'),
            'sender' => env('SMS_SENDER_SMSC_SENDER'),
        ],
        'sms-sender-pilot' => [
            'api-key' => env('SMS_SENDER_PILOT_API_KEY'),
            'sender' => env('SMS_SENDER_PILOT_SENDER'),
        ],
    ],
];
