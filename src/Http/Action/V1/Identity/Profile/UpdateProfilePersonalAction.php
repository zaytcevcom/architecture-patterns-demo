<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Profile;

use App\Modules\Identity\Command\UpdatePersonal\IdentityUpdatePersonalCommand;
use App\Modules\Identity\Command\UpdatePersonal\IdentityUpdatePersonalHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Put(
    path: '/identity/profile/personal',
    description: 'Обновление личной информации профиля',
    summary: 'Обновление личной информации профиля',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'marital',
                    type: 'integer',
                    example: 4,
                ),
                new OA\Property(
                    property: 'maritalId',
                    type: 'integer',
                    example: 1,
                ),
                new OA\Property(
                    property: 'contactCountryId',
                    type: 'integer',
                    example: 135,
                ),
                new OA\Property(
                    property: 'contactCityId',
                    type: 'integer',
                    example: 602,
                ),
                new OA\Property(
                    property: 'contactPhone',
                    type: 'string',
                    example: '79999999999'
                ),
                new OA\Property(
                    property: 'contactSite',
                    type: 'string',
                    example: 'lo.ink'
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
final readonly class UpdateProfilePersonalAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityUpdatePersonalHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                ['userId' => $identity->id]
            ),
            IdentityUpdatePersonalCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
