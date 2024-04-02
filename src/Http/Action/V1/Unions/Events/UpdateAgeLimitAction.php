<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Events;

use App\Modules\Union\Command\Event\Management\UpdateAgeLimit\EventUpdateAgeLimitCommand;
use App\Modules\Union\Command\Event\Management\UpdateAgeLimit\EventUpdateAgeLimitHandler;
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
    path: '/events/{id}/age-limit',
    description: 'Редактирование возрастного рейтинга события',
    summary: 'Редактирование возрастного рейтинга события',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'ageLimit',
                    type: 'integer',
                    example: 16
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
final readonly class UpdateAgeLimitAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private EventUpdateAgeLimitHandler $handler,
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
            EventUpdateAgeLimitCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
