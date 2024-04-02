<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Modules\Union\Command\Management\PhotoSave\PhotoSaveCommand;
use App\Modules\Union\Command\Management\PhotoSave\PhotoSaveHandler;
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

#[OA\Post(
    path: '/unions/{id}/photo',
    description: 'Сохранение загруженного аватара объединения',
    summary: 'Сохранение загруженного аватара объединения',
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
    tags: ['Unions (Management)'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class SavePhotoAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PhotoSaveHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                [
                    'userId' => $identity->id,
                    'unionId' => Route::getArgumentToInt($request, 'id'),
                ]
            ),
            PhotoSaveCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
