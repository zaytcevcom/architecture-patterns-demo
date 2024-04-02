<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Contacts;

use App\Modules\Union\Command\Contact\Create\UnionContactCreateCommand;
use App\Modules\Union\Command\Contact\Create\UnionContactCreateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/unions/{id}/contacts',
    description: 'Добавление контакта<br><br>
    **Коды ошибок**:<br>
    **1** - Доступ запрещен<br>
    **2** - Достигнуто ограничение на максимальное кол-во контактов<br>',
    summary: 'Добавление контакта',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'userId',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'position',
                    type: 'string',
                    example: 'CEO'
                ),
                new OA\Property(
                    property: 'phone',
                    type: 'string',
                    example: null
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    example: 'test@test.com'
                ),
            ]
        )
    ),
    tags: ['Unions (Contact)']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class CreateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionContactCreateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'sourceId' => $identity->id,
                'unionId' => Route::getArgumentToInt($request, 'id'),
            ]),
            UnionContactCreateCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
