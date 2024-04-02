<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function App\Components\env;

return [
    ValidatorInterface::class => static function (ContainerInterface $container): ValidatorInterface {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     cache_dir: string|null,
         * } $settings
         */
        $settings = $container->get('config')['validator'];

        $translator = $container->get(TranslatorInterface::class);

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setTranslator($translator)
            ->setTranslationDomain('validators');

        if (null !== $settings['cache_dir']) {
            $validator->setMappingCache(
                new FilesystemAdapter('', 0, $settings['cache_dir'])
            );
        }

        return $validator->getValidator();
    },

    'config' => [
        'validator' => [
            'cache_dir' => (env('APP_ENV') !== 'dev') ? __DIR__ . '/../../var/cache/validator' : null,
        ],
    ],
];
