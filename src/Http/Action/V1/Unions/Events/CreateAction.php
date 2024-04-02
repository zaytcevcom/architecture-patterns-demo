<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Events;

use App\Modules\Union\Command\Event\Create\EventCreateCommand;
use App\Modules\Union\Command\Event\Create\EventCreateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/events',
    description: 'Создание события',
    summary: 'Создание события',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'placeId',
                    type: 'integer',
                    example: 1
                ),
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Новое событие!'
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
                    property: 'dates',
                    type: 'array',
                    items: new OA\Items(),
                    example: [
                        [
                            'timeStart' => 1691490950,
                            'timeEnd'   => 1691505350,
                        ],
                        [
                            'timeStart' => 1691577350,
                            'timeEnd'   => 1691591750,
                        ],
                    ]
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
            ]
        )
    ),
    tags: ['Events'],
    responses: [new ResponseSuccessful()]
)]
final readonly class CreateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private EventCreateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'creatorId' => $identity->id,
            ]),
            EventCreateCommand::class
        );

        $this->validator->validate($command);

        $unionId = $this->handler->handle($command);

        return new JsonDataResponse([
            'id' => $unionId,
        ]);
    }
}
