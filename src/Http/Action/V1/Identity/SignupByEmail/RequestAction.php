<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\SignupByEmail;

use App\Modules\Identity\Command\SignupByEmail\Request\IdentitySignupByEmailRequestCommand;
use App\Modules\Identity\Command\SignupByEmail\Request\IdentitySignupByEmailRequestHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/signup/email/request',
    description: 'Начало регистрации по email.<br><br>
        В ответе будут получены:<br>
        - **uniqueId** - необходимо передать в метод /identity/signup/email/confirm<br>
        - **interval** - кол-во секунд, через которые можно снова отправить код подтверждения<br><br>
        **Коды ошибок**<br>
        **1** - Метод не доступен<br>
        **2** - Пользователь уже существует<br>
        **3** - Пользователь уже существует, но можно его деактивировать (более 2х лет)<br>
        **4** - Слишком частые запросы на получение кода подтверждения<br>
    ',
    summary: 'Начало регистрации по email',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'firstName',
                    type: 'string',
                    example: 'Константин'
                ),
                new OA\Property(
                    property: 'lastName',
                    type: 'string',
                    example: 'Зайцев'
                ),
                new OA\Property(
                    property: 'sex',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: '1234567890'
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    example: 'zaydisk@yandex.ru'
                ),
                new OA\Property(
                    property: 'photoHost',
                    type: 'string',
                    example: null
                ),
                new OA\Property(
                    property: 'photoFileId',
                    type: 'string',
                    example: null
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
    tags: ['Identity (Signup by email)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RequestAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentitySignupByEmailRequestHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize($request->getParsedBody(), IdentitySignupByEmailRequestCommand::class);

        $this->validator->validate($command);

        $result = $this->handler->handle($command);

        return new JsonDataResponse(
            data: $result
        );
    }
}
