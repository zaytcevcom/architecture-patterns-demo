<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Profile;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Identity\Command\PhotoSave\PhotoSaveCommand;
use App\Modules\Identity\Command\PhotoSave\PhotoSaveHandler;
use App\Modules\Identity\Query\GetById\IdentityGetByIdFetcher;
use App\Modules\Identity\Query\GetById\IdentityGetByIdQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/profile/photo',
    description: 'Сохранение загруженного аватара пользователя',
    summary: 'Сохранение загруженного аватара пользователя',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'host',
                    type: 'string',
                    example: 'https://host.com'
                ),
                new OA\Property(
                    property: 'fileId',
                    type: 'string',
                    example: '1659255729.f2a3cf7ef6cd7808c493571ccf40680'
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
final readonly class ProfileSavePhotoAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PhotoSaveHandler $handler,
        private Validator $validator,
        private IdentityGetByIdFetcher $fetcher,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), ['userId' => $identity->id]),
            PhotoSaveCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        $result = $this->fetcher->fetch(
            new IdentityGetByIdQuery($identity->id)
        );

        return new JsonDataResponse(
            $this->unifier->unifyOne($identity->id, $result)
        );
    }
}
