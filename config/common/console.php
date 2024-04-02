<?php

declare(strict_types=1);

use App\Modules\Audio\Console\DeleteOldAudiosCommand;
use App\Modules\Identity\Console\Typesense\UserRefreshCommand;
use App\Modules\OAuth\Console\ClearExpiredCommand;
use App\Modules\Union\Console\Typesense\CommunityRefreshCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Psr\Container\ContainerInterface;

return [
    EntityManagerProvider::class => static fn (ContainerInterface $container): EntityManagerProvider => new SingleManagerProvider($container->get(EntityManagerInterface::class)),

    ValidateSchemaCommand::class => static fn (ContainerInterface $container): ValidateSchemaCommand => new ValidateSchemaCommand($container->get(EntityManagerProvider::class)),

    MetadataCommand::class => static function (ContainerInterface $container): MetadataCommand {
        return new MetadataCommand($container->get(EntityManagerProvider::class));
    },

    QueryCommand::class => static function (ContainerInterface $container): QueryCommand {
        return new QueryCommand($container->get(EntityManagerProvider::class));
    },

    ResultCommand::class => static function (ContainerInterface $container): ResultCommand {
        return new ResultCommand($container->get(EntityManagerProvider::class));
    },

    GenerateProxiesCommand::class => static function (ContainerInterface $container): GenerateProxiesCommand {
        return new GenerateProxiesCommand($container->get(EntityManagerProvider::class));
    },

    'config' => [
        'console' => [
            'commands' => [
                ValidateSchemaCommand::class,

                ExecuteCommand::class,
                MigrateCommand::class,
                LatestCommand::class,
                ListCommand::class,
                StatusCommand::class,
                UpToDateCommand::class,

                MetadataCommand::class,
                QueryCommand::class,
                ResultCommand::class,
                GenerateProxiesCommand::class,

                ClearExpiredCommand::class,

                CommunityRefreshCommand::class,
                UserRefreshCommand::class,

                DeleteOldAudiosCommand::class,

            ],
        ],
    ],
];
