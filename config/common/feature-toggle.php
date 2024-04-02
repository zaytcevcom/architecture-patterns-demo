<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ZayMedia\Shared\Components\FeatureToggle\FeatureFlag;
use ZayMedia\Shared\Components\FeatureToggle\Features;
use ZayMedia\Shared\Components\FeatureToggle\FeaturesContext;
use ZayMedia\Shared\Components\FeatureToggle\FeatureSwitch;

return [
    FeatureFlag::class => DI\get(Features::class),
    FeatureSwitch::class => DI\get(Features::class),
    FeaturesContext::class => DI\get(Features::class),

    Features::class => static function (ContainerInterface $container): Features {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{features: array<string, bool>} $config
         */
        $config = $container->get('config')['feature-toggle'];

        return new Features($config['features']);
    },

    'config' => [
        'feature-toggle' => [
            'features' => [
                'IS_DEV' => false,
            ],
        ],
    ],
];
