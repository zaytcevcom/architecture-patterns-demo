<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity;

use App\Modules\Identity\Command\ReSend\IdentityReSendCommand;
use App\Modules\Identity\Command\ReSend\IdentityReSendHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\IpAddress;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/signup/resend',
    description: 'Повторная отправка кода подтверждения<br><br>
        В ответе будут получены:<br>
        - **response** - статус отправки сообщения<br>
        - **interval** - кол-во секунд, через которые можно снова отправить код подтверждения<br><br>
        **Коды ошибок**<br>
        **1** - Информация о начале регистрации не найдена<br>
        **2** - Слишком частые запросы на получение кода подтверждения<br>
    ',
    summary: 'Повторная отправка кода подтверждения',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'uniqueId',
                    type: 'string',
                    example: '561c5132-ef7f-4dc1-9c2d-af7032daec4e'
                ),
            ]
        )
    ),
    tags: ['Identity (Signup)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class ReSendAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityReSendHandler $handler,
        private Validator $validator
    ) {}

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
            IdentityReSendCommand::class
        );

        $this->validator->validate($command);

        $result = $this->handler->handle($command);

        return new JsonDataResponse(
            data: $result
        );
    }
}
