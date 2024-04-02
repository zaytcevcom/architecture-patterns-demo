<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Location;

use App\Modules\Identity\Command\Location\IdentityUpdateLocationCommand;
use App\Modules\Identity\Command\Location\IdentityUpdateLocationHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Put(
    path: '/identity/location',
    description: 'Обновление данных о местоположении',
    summary: 'Обновление данных о местоположении',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'latitude',
                    type: 'float',
                    example: 52.5072747
                ),
                new OA\Property(
                    property: 'longitude',
                    type: 'float',
                    example: 85.1472004
                ),
            ]
        )
    ),
    tags: ['Identity']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class UpdateLocationAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityUpdateLocationHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            data: array_merge(
                (array)$request->getParsedBody(),
                ['userId' => $identity->id]
            ),
            type: IdentityUpdateLocationCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
