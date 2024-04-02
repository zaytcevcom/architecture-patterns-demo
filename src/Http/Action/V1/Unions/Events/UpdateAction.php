<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Events;

use App\Modules\Union\Command\Event\Management\Update\EventUpdateCommand;
use App\Modules\Union\Command\Event\Management\Update\EventUpdateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Put(
    path: '/events/{id}',
    description: 'Редактирование события',
    summary: 'Редактирование события',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Новое наименование!'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    example: null
                ),
                new OA\Property(
                    property: 'categoryId',
                    type: 'integer',
                    example: 8
                ),
                new OA\Property(
                    property: 'website',
                    type: 'string',
                    example: null
                ),
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    example: null
                ),
            ]
        )
    ),
    tags: ['Events'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор события',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class UpdateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private EventUpdateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'userId' => $identity->id,
                'unionId' => Route::getArgumentToInt($request, 'id'),
            ]),
            EventUpdateCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
