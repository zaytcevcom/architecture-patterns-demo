<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Space;

use App\Modules\Identity\Command\Space\IdentityUpdateSpaceCommand;
use App\Modules\Identity\Command\Space\IdentityUpdateSpaceHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Put(
    path: '/identity/space',
    description: 'Обновление данных о пространстве пользователя',
    summary: 'Обновление данных о пространстве пользователя',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'spaceId',
                    type: 'integer',
                    example: null
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
final readonly class UpdateSpaceAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityUpdateSpaceHandler $handler,
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
            type: IdentityUpdateSpaceCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
