<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Contacts;

use App\Modules\Union\Command\Contact\Update\UnionContactUpdateCommand;
use App\Modules\Union\Command\Contact\Update\UnionContactUpdateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Put(
    path: '/unions/{id}/contacts/{contactId}',
    description: 'Редактирование контакта',
    summary: 'Редактирование контакта',
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
#[OA\Parameter(
    name: 'contactId',
    description: 'Идентификатор контакта',
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
final readonly class UpdateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionContactUpdateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'sourceId' => $identity->id,
                'unionId' => Route::getArgumentToInt($request, 'id'),
                'contactId' => Route::getArgumentToInt($request, 'contactId'),
            ]),
            UnionContactUpdateCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
