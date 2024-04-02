<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Token;

use App\Modules\Identity\Command\SetOnline\IdentitySetOnlineHandler;
use App\Modules\OAuth\Command\AddDeviceInfoToRefreshToken\AddDeviceInfoToRefreshTokenCommand;
use App\Modules\OAuth\Command\AddDeviceInfoToRefreshToken\AddDeviceInfoToRefreshTokenHandler;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\JWTParser;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;
use ZayMedia\Shared\Http\Middleware\IpAddress;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/token',
    description: '**Grant — Password** (Получения токенов доступа по логину и паролю).<br><br>
        **Обязательные параметры:**<br>
        - grant_type = **password**<br>
        - client_id<br>
        - username<br>
        - password<br>
        <br><br>
        **Grant — Refresh Token** (Обновление токенов доступа)<br><br>
        **Обязательные параметры:**<br>
        - grant_type = **refresh_token**<br>
        - client_id<br>
        - refresh_token<br>
        <br><br>
        **Grant — Authorization Code** (Обмен кода авторизации на токены доступа)<br><br>
        **Обязательные параметры:**<br>
        - grant_type = **authorization_code**<br>
        - client_id<br>
        - redirect_uri<br>
        - code<br>
        - code_verifier<br>
    ',
    summary: 'Авторизация',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'grant_type',
                    type: 'string',
                    example: 'password'
                ),
                new OA\Property(
                    property: 'client_id',
                    type: 'string',
                    example: '5'
                ),
                new OA\Property(
                    property: 'username',
                    type: 'string',
                    example: '79999999999'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: '1234567890'
                ),
                new OA\Property(
                    property: 'pushToken',
                    type: 'string',
                    example: '8F3F9678B591116F8338F7BD4FDC5BD1C8A8B399D7E50B06F1869EA1E1B7606C'
                ),
                new OA\Property(
                    property: 'voipToken',
                    type: 'string',
                    example: '8F3F9678B591116F8338F7BD4FDC5BD1C8A8B399D7E50B06F1869EA1E1B7606'
                ),
                new OA\Property(
                    property: 'baseOS',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'buildId',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'brand',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'buildNumber',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'bundleId',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'carrier',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'deviceId',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'deviceName',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'ipAddress',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'installerPackageName',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'macAddress',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'manufacturer',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'model',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'systemName',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'systemVersion',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'userAgent',
                    type: 'string',
                    example: 'xxx'
                ),
                new OA\Property(
                    property: 'version',
                    type: 'string',
                    example: 'xxx'
                ),
            ]
        )
    ),
    tags: ['Identity']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class TokenAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private AuthorizationServer $server,
        private ResponseFactoryInterface $response,
        private JWTParser $JWTParser,
        private Translator $translator,
        private AddDeviceInfoToRefreshTokenHandler $addDeviceInfoToRefreshTokenHandler,
        private IdentitySetOnlineHandler $onlineHandler,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->server->respondToAccessTokenRequest($request, $this->response->createResponse());

            /** @var array{access_token: string, refresh_token:string} $data */
            $data = json_decode((string)$response->getBody(), true);
        } catch (Exception $exception) {
            throw new DomainExceptionModule(
                module: 'oauth',
                message: $exception->getMessage(),
                code: (int)$exception->getCode()
            );
        }

        $this->setDeviceInfo($request, $data);

        $this->setOnline($data['access_token']);

        return new JsonDataResponse($data);
    }

    /** @throws EnvironmentIsBrokenException|ExceptionInterface|WrongKeyOrModifiedCiphertextException */
    private function setDeviceInfo(ServerRequestInterface $request, array $data): void
    {
        /**
         * @var array{access_token: string, refresh_token:string} $data
         * @var array{refresh_token_id: string} $jwtRefreshToken
         */
        $jwtRefreshToken = $this->JWTParser->parseRefreshToken($data['refresh_token']);

        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                [
                    'identifier' => $jwtRefreshToken['refresh_token_id'],
                    'locale' => $this->translator->getLocale(),
                    'ipReal' => IpAddress::getReal($request),
                    'ipAddress' => IpAddress::get($request),
                ]
            ),
            AddDeviceInfoToRefreshTokenCommand::class
        );

        $this->addDeviceInfoToRefreshTokenHandler->handle($command);
    }

    /** @throws OAuthServerException */
    private function setOnline(string $access_token): void
    {
        $jwt = $this->JWTParser->parse($access_token);
        $this->onlineHandler->handle((int)$jwt->get('sub'));
    }
}
