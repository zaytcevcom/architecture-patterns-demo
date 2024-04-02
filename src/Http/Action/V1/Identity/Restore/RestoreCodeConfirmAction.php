<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Restore;

use App\Modules\Identity\Command\Restore\CodeConfirm\IdentityRestoreCodeConfirmCommand;
use App\Modules\Identity\Command\Restore\CodeConfirm\IdentityRestoreCodeConfirmHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/identity/restore/code/confirm',
    description: 'Шаг 3. Проверка кода подтверждения.<br><br>
        **Коды ошибок**<br>
        **1** - Информация о начале восстановления не найдена<br>
        **2** - Неверный код подтверждения<br>
    ',
    summary: 'Шаг 3. Проверка кода подтверждения',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'uniqueId',
                    type: 'string',
                    example: '561c5132-ef7f-4dc1-9c2d-af7032daec4e'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'string',
                    example: '55666'
                ),
            ]
        )
    ),
    tags: ['Identity (Restore)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RestoreCodeConfirmAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityRestoreCodeConfirmHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize($request->getParsedBody(), IdentityRestoreCodeConfirmCommand::class);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
