<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Places;

use App\Modules\Union\Command\Place\Create\PlaceCreateCommand;
use App\Modules\Union\Command\Place\Create\PlaceCreateHandler;
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
    path: '/places',
    description: 'Создание места',
    summary: 'Создание места',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Новое места!'
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
                    property: 'cityId',
                    type: 'integer',
                    example: 602
                ),
                new OA\Property(
                    property: 'address',
                    type: 'string',
                    example: 'г Москва, Ленинградский пр-кт'
                ),
                new OA\Property(
                    property: 'latitude',
                    type: 'float',
                    example: 55.794285
                ),
                new OA\Property(
                    property: 'longitude',
                    type: 'float',
                    example: 37.545635
                ),
                new OA\Property(
                    property: 'workingHours',
                    type: 'array',
                    items: new OA\Items(),
                    example: [
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
                        ],
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
                        ],
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
                        ],
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
                        ],
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
                        ],
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
                        ],
                        [
                            'from'      => 32400,
                            'to'        => 64800,
                            'closed'    => false,
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
    tags: ['Places'],
    responses: [new ResponseSuccessful()]
)]
final readonly class CreateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PlaceCreateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'creatorId' => $identity->id,
            ]),
            PlaceCreateCommand::class
        );

        $this->validator->validate($command);

        $unionId = $this->handler->handle($command);

        return new JsonDataResponse([
            'id' => $unionId,
        ]);
    }
}
