<?php

declare(strict_types=1);

use App\Modules\Audio\Entity\Audio\Fields\Doctrine\SourceFileIdType;
use App\Modules\Audio\Entity\Audio\Fields\Doctrine\SourceHostType;
use App\Modules\Audio\Entity\Audio\Fields\Doctrine\SourceType;
use App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoAnimatedFileIdType;
use App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoAnimatedHostType;
use App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoAnimatedType;
use App\Modules\Identity\Entity\SignupMethod\Fields\Doctrine\StatusType;
use App\Modules\Identity\Entity\User\Fields\Doctrine;
use App\Modules\Identity\Entity\User\Fields\Doctrine\BirthdayType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\BlockedType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\CodeType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\DeactivatedType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\DeletedType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\EmailType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\FirstNameType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\LastNameType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\MaritalType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\PhoneType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\ScreenNameType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\SexType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\SiteType;
use App\Modules\Identity\Entity\User\Fields\Doctrine\VerifiedType;
use App\Modules\Union\Entity\Union\Fields\Doctrine\CoverFileIdType;
use App\Modules\Union\Entity\Union\Fields\Doctrine\CoverHostType;
use App\Modules\Union\Entity\Union\Fields\Doctrine\CoverType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use function App\Components\env;

return [
    EntityManagerInterface::class => static function (ContainerInterface $container): EntityManager {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     metadata_dirs:string[],
         *     dev_mode:bool,
         *     proxy_dir:string,
         *     cache_dir:string|null,
         *     types:array<string,class-string<Type>>,
         *     subscribers:string[],
         *     connection:array<string, mixed>
         * } $settings
         */
        $settings = $container->get('config')['doctrine'];

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $settings['metadata_dirs'],
            isDevMode: $settings['dev_mode'],
            proxyDir: $settings['proxy_dir'],
            cache: $settings['cache_dir'] ? new FilesystemAdapter('', 0, $settings['cache_dir']) : new ArrayAdapter()
        );

        $config->setNamingStrategy(new UnderscoreNamingStrategy());

        foreach ($settings['types'] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        $connection = DriverManager::getConnection(
            $settings['connection'],
            $config
        );

        return new EntityManager($connection, $config);
    },

    Connection::class => static function (ContainerInterface $container): Connection {
        $em = $container->get(EntityManagerInterface::class);
        return $em->getConnection();
    },

    'config' => [
        'doctrine' => [
            'dev_mode' => false,
            'cache_dir' => __DIR__ . '/../../var/cache/doctrine/cache',
            'proxy_dir' => __DIR__ . '/../../var/cache/doctrine/proxy',
            'connection' => [
                'driver' => env('DB_DRIVER'),
                'host' => env('DB_HOST'),
                'user' => env('DB_USER'),
                'password' => env('DB_PASSWORD'),
                'dbname' => env('DB_NAME'),
                'charset' => env('DB_CHARSET'),
            ],
            'metadata_dirs' => [
                __DIR__ . '/../../src/Modules/OAuth/Entity',
                __DIR__ . '/../../src/Modules/Identity/Entity',
                __DIR__ . '/../../src/Modules/Data/Entity',
                __DIR__ . '/../../src/Modules/Contact/Entity',
                __DIR__ . '/../../src/Modules/Photo/Entity',
                __DIR__ . '/../../src/Modules/System/Entity',
                __DIR__ . '/../../src/Modules/Storage/Entity',
                __DIR__ . '/../../src/Modules/Audio/Entity',
                __DIR__ . '/../../src/Modules/Banner/Entity',
                __DIR__ . '/../../src/Modules/Union/Entity',
                __DIR__ . '/../../src/Modules/Complaint/Entity',
                __DIR__ . '/../../src/Modules/Post/Entity',
                __DIR__ . '/../../src/Modules/Messenger/Entity',
                __DIR__ . '/../../src/Modules/Calls/Entity',
                __DIR__ . '/../../src/Modules/Media/Entity',
                __DIR__ . '/../../src/Modules/Sticker/Entity',
                __DIR__ . '/../../src/Modules/Flow/Entity',
                __DIR__ . '/../../src/Modules/Legacy/Entity',
                __DIR__ . '/../../src/Modules/ShadowBan/Entity',
                __DIR__ . '/../../src/Modules/Wallet/Entity',
                __DIR__ . '/../../src/Modules/_Features/Entity',
                __DIR__ . '/../../src/Modules/AutoPosting/Entity',
            ],
            'types' => [
                // Module Identity
                EmailType::NAME => EmailType::class,
                PhoneType::NAME => PhoneType::class,
                ScreenNameType::NAME => ScreenNameType::class,
                FirstNameType::NAME => FirstNameType::class,
                LastNameType::NAME => LastNameType::class,
                SexType::NAME => SexType::class,
                Doctrine\PhotoHostType::NAME => Doctrine\PhotoHostType::class,
                Doctrine\PhotoFileIdType::NAME => Doctrine\PhotoFileIdType::class,
                CodeType::NAME => CodeType::class,
                MaritalType::NAME => MaritalType::class,
                Doctrine\PhotoType::NAME => Doctrine\PhotoType::class,
                SiteType::NAME => SiteType::class,
                VerifiedType::NAME => VerifiedType::class,
                DeactivatedType::NAME => DeactivatedType::class,
                DeletedType::NAME => DeletedType::class,
                BlockedType::NAME => BlockedType::class,
                BirthdayType::NAME => BirthdayType::class,
                StatusType::NAME => StatusType::class,

                SourceType::NAME => SourceType::class,
                SourceHostType::NAME => SourceHostType::class,
                SourceFileIdType::NAME => SourceFileIdType::class,

                App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoType::NAME => App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoType::class,
                App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoHostType::NAME => App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoHostType::class,
                App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoFileIdType::NAME => App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine\PhotoFileIdType::class,

                PhotoAnimatedType::NAME => PhotoAnimatedType::class,
                PhotoAnimatedHostType::NAME => PhotoAnimatedHostType::class,
                PhotoAnimatedFileIdType::NAME => PhotoAnimatedFileIdType::class,

                App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine\PhotoType::NAME => App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine\PhotoType::class,
                App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine\PhotoHostType::NAME => App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine\PhotoHostType::class,
                App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine\PhotoFileIdType::NAME => App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine\PhotoFileIdType::class,

                App\Modules\Union\Entity\Union\Fields\Doctrine\PhotoType::NAME => App\Modules\Union\Entity\Union\Fields\Doctrine\PhotoType::class,
                App\Modules\Union\Entity\Union\Fields\Doctrine\PhotoHostType::NAME => App\Modules\Union\Entity\Union\Fields\Doctrine\PhotoHostType::class,
                App\Modules\Union\Entity\Union\Fields\Doctrine\PhotoFileIdType::NAME => App\Modules\Union\Entity\Union\Fields\Doctrine\PhotoFileIdType::class,

                CoverType::NAME => CoverType::class,
                CoverHostType::NAME => CoverHostType::class,
                CoverFileIdType::NAME => CoverFileIdType::class,
            ],
        ],
    ],
];
