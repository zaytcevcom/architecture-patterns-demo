<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\SignupByPhone;

use App\Modules\Identity\Command\SignupByPhone\Request\IdentitySignupByPhoneRequestCommand;
use App\Modules\Identity\Command\SignupByPhone\Request\IdentitySignupByPhoneRequestHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\IpAddress;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/signup/phone/request',
    description: 'Шаг 1. Начало регистрации по sms.<br><br>
        В ответе будут получены:<br>
        - **uniqueId** - необходимо передать в метод /identity/signup/phone/captcha<br>
        - **captcha** - ответ на капчу необходимо передать в поле code метода /identity/signup/phone/captcha<br><br>
        **Коды ошибок**<br>
        **1** - Метод не доступен<br>
        **2** - Пользователь уже существует<br>
        **3** - Пользователь уже существует, но можно его деактивировать (более 2х лет)<br>
    ',
    summary: 'Шаг 1. Начало регистрации по sms',
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
                    property: 'phone',
                    type: 'string',
                    example: '+79999999999'
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
    tags: ['Identity (Signup by phone)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RequestAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentitySignupByPhoneRequestHandler $handler,
        private Validator $validator
    ) {}

    /** @throws ExceptionInterface */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                [
                    'ipReal' => IpAddress::getReal($request),
                    'ipAddress' => IpAddress::get($request),
                ]
            ),
            IdentitySignupByPhoneRequestCommand::class
        );

        $this->validator->validate($command);

        $result = $this->handler->handle($command);

        return new JsonDataResponse(
            data: $result
        );
    }
}
