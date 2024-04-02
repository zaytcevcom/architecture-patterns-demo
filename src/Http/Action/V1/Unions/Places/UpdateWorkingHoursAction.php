<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Places;

use App\Modules\Union\Command\Place\Management\UpdateWorkingHours\PlaceUpdateWorkingHoursCommand;
use App\Modules\Union\Command\Place\Management\UpdateWorkingHours\PlaceUpdateWorkingHoursHandler;
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
    path: '/places/{id}/working-hours',
    description: 'Редактирование времени работы места',
    summary: 'Редактирование времени работы места',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
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
final readonly class UpdateWorkingHoursAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PlaceUpdateWorkingHoursHandler $handler,
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
            PlaceUpdateWorkingHoursCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
