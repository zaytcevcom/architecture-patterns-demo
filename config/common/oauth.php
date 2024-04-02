<?php

declare(strict_types=1);

use App\Modules\OAuth\Entity\AccessTokenRepository;
use App\Modules\OAuth\Entity\AuthCode;
use App\Modules\OAuth\Entity\AuthCodeRepository;
use App\Modules\OAuth\Entity\Client;
use App\Modules\OAuth\Entity\ClientRepository;
use App\Modules\OAuth\Entity\RefreshToken;
use App\Modules\OAuth\Entity\RefreshTokenRepository;
use App\Modules\OAuth\Entity\Scope;
use App\Modules\OAuth\Entity\ScopeRepository;
use App\Modules\OAuth\Entity\UserRepository;
use App\Modules\OAuth\Generator\AccessTokenGenerator;
use App\Modules\OAuth\Generator\BearerTokenGenerator;
use App\Modules\OAuth\Generator\RefreshTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use ZayMedia\Shared\Components\JWTParser;
use ZayMedia\Shared\Http\Middleware\Identity\BearerTokenValidator;

use function App\Components\env;

return [
    AuthorizationServer::class => static function (ContainerInterface $container): AuthorizationServer {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *    private_key_path:string,
         *    encryption_key:string,
         *    auth_code_interval:string,
         *    access_token_interval:string,
         *    refresh_token_interval:string,
         * } $config
         */
        $config = $container->get('config')['oauth'];

        $clientRepository = $container->get(ClientRepositoryInterface::class);
        $scopeRepository = $container->get(ScopeRepositoryInterface::class);
        $accessTokenRepository = $container->get(AccessTokenRepositoryInterface::class);
        $authCodeRepository = $container->get(AuthCodeRepositoryInterface::class);
        $userRepository = $container->get(UserRepositoryInterface::class);
        $refreshTokenRepository = $container->get(RefreshTokenRepositoryInterface::class);

        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            new CryptKey($config['private_key_path'], null, false),
            $config['encryption_key']
        );

        $grant = new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new DateInterval($config['auth_code_interval']));
        $grant->setRefreshTokenTTL(new DateInterval($config['refresh_token_interval']));
        $server->enableGrantType($grant, new DateInterval($config['access_token_interval']));

        $grant = new PasswordGrant($userRepository, $refreshTokenRepository);
        $grant->setRefreshTokenTTL(new DateInterval($config['refresh_token_interval']));
        $server->enableGrantType($grant, new DateInterval($config['access_token_interval']));

        $grant = new RefreshTokenGrant($refreshTokenRepository);
        $grant->setRefreshTokenTTL(new DateInterval($config['refresh_token_interval']));
        $server->enableGrantType($grant, new DateInterval($config['access_token_interval']));

        return $server;
    },
    ResourceServer::class => static function (ContainerInterface $container): ResourceServer {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *    public_key_path:string
         * } $config
         */
        $config = $container->get('config')['oauth'];

        $repository = $container->get(AccessTokenRepositoryInterface::class);
        $publicKey = new CryptKey($config['public_key_path'], null, false);

        $validator = new BearerTokenValidator($repository);
        $validator->setPublicKey($publicKey);

        return new ResourceServer(
            $repository,
            $publicKey,
            $validator
        );
    },
    JWTParser::class => static function (ContainerInterface $container): JWTParser {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *    public_key_path:string,
         *    encryption_key:string
         * } $config
         */
        $config = $container->get('config')['oauth'];

        $publicKey = new CryptKey($config['public_key_path'], null, false);

        $parser = new JWTParser($publicKey);
        $parser->setEncryptionKey($config['encryption_key']);

        return $parser;
    },
    ScopeRepositoryInterface::class => static function (ContainerInterface $container): ScopeRepository {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-var array{scopes: string[]} $config
         */
        $config = $container->get('config')['oauth'];

        return new ScopeRepository(
            array_map(static fn (string $item): Scope => new Scope($item), $config['scopes'])
        );
    },
    ClientRepositoryInterface::class => static function (ContainerInterface $container): ClientRepository {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-var array{
         *     clients: array<array-key, array{
         *         name: string,
         *         client_id: string,
         *         redirect_uri: string
         *     }>
         * } $config
         */
        $config = $container->get('config')['oauth'];

        return new ClientRepository(
            array_map(static function (array $item): Client {
                return new Client(
                    $item['client_id'],
                    $item['name'],
                    $item['redirect_uri']
                );
            }, $config['clients'])
        );
    },
    UserRepositoryInterface::class => DI\get(UserRepository::class),
    AccessTokenRepositoryInterface::class => DI\get(AccessTokenRepository::class),
    AuthCodeRepositoryInterface::class => DI\get(AuthCodeRepository::class),
    AuthCodeRepository::class => static function (ContainerInterface $container): AuthCodeRepository {
        $em = $container->get(EntityManagerInterface::class);
        $repo = $em->getRepository(AuthCode::class);
        return new AuthCodeRepository($em, $repo);
    },
    RefreshTokenRepositoryInterface::class => DI\get(RefreshTokenRepository::class),
    RefreshTokenRepository::class => static function (ContainerInterface $container): RefreshTokenRepository {
        $em = $container->get(EntityManagerInterface::class);
        $repo = $em->getRepository(RefreshToken::class);
        return new RefreshTokenRepository($em, $repo);
    },
    AccessTokenGenerator::class => static function (ContainerInterface $container): AccessTokenGenerator {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *    private_key_path:string,
         * } $config
         */
        $config = $container->get('config')['oauth'];

        return new AccessTokenGenerator($config['private_key_path']);
    },
    BearerTokenGenerator::class => static function (ContainerInterface $container): BearerTokenGenerator {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *    encryption_key:string,
         * } $config
         */
        $config = $container->get('config')['oauth'];

        $accessTokenGenerator = $container->get(AccessTokenGenerator::class);
        $refreshTokenGenerator = $container->get(RefreshTokenGenerator::class);

        return new BearerTokenGenerator(
            accessTokenGenerator: $accessTokenGenerator,
            refreshTokenGenerator: $refreshTokenGenerator,
            encryptionKeyPath: $config['encryption_key']
        );
    },

    'config' => [
        'oauth' => [
            'scopes' => [
                'common',
            ],
            'clients' => [
                [
                    'name' => 'SERVER',
                    'client_id' => '1',
                    'redirect_uri' => 'default',
                ],
                [
                    'name' => 'WEB',
                    'client_id' => '2',
                    'redirect_uri' => 'default',
                ],
                [
                    'name' => 'iOS',
                    'client_id' => '5',
                    'redirect_uri' => 'default',
                ],
                [
                    'name' => 'Android',
                    'client_id' => '6',
                    'redirect_uri' => 'default',
                ],
                [
                    'name' => 'Eventor',
                    'client_id' => '7',
                    'redirect_uri' => 'https://default',
                ],
            ],
            'encryption_key' => env('JWT_ENCRYPTION_KEY'),
            'public_key_path' => env('JWT_PUBLIC_KEY_PATH'),
            'private_key_path' => env('JWT_PRIVATE_KEY_PATH'),
            'auth_code_interval' => 'PT1M',
            'access_token_interval' => 'PT5M',
            'refresh_token_interval' => 'P90D',
        ],
    ],
];
