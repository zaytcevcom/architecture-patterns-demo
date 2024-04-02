<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Places;

use App\Modules\Union\Command\Place\Management\UpdateAddress\PlaceUpdateAddressCommand;
use App\Modules\Union\Command\Place\Management\UpdateAddress\PlaceUpdateAddressHandler;
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
    path: '/places/{id}/address',
    description: 'Редактирование адреса места',
    summary: 'Редактирование адреса места',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
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
            ]
        )
    ),
    tags: ['Places'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор места',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class UpdateAddressAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PlaceUpdateAddressHandler $handler,
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
            PlaceUpdateAddressCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
