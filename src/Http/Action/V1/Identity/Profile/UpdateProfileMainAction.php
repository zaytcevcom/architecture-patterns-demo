<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Profile;

use App\Modules\Identity\Command\UpdateMain\IdentityUpdateMainCommand;
use App\Modules\Identity\Command\UpdateMain\IdentityUpdateMainHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Patch(
    path: '/identity/profile/main',
    description: 'Обновление основной информации профиля',
    summary: 'Обновление основной информации профиля',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'firstName',
                    type: 'string',
                    example: 'Иван'
                ),
                new OA\Property(
                    property: 'lastName',
                    type: 'string',
                    example: 'Иванов'
                ),
                new OA\Property(
                    property: 'birthday',
                    type: 'string',
                    example: '827107200'
                ),
                new OA\Property(
                    property: 'countryId',
                    type: 'integer',
                    example: 135,
                ),
                new OA\Property(
                    property: 'cityId',
                    type: 'integer',
                    example: 602,
                ),
                new OA\Property(
                    property: 'sex',
                    type: 'integer',
                    example: 0,
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
final readonly class UpdateProfileMainAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityUpdateMainHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), ['userId' => $identity->id]),
            IdentityUpdateMainCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
