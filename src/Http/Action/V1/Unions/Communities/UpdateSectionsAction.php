<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Communities;

use App\Modules\Union\Command\Community\Management\UpdateSections\CommunityUpdateSectionsCommand;
use App\Modules\Union\Command\Community\Management\UpdateSections\CommunityUpdateSectionsHandler;
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
    path: '/communities/{id}/sections',
    description: 'Редактирование разделов сообщества',
    summary: 'Редактирование разделов сообщества',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'posts',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'photos',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'videos',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'audios',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'contacts',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'links',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'messages',
                    type: 'boolean',
                    example: true
                ),
            ]
        )
    ),
    tags: ['Communities'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор сообщества',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class UpdateSectionsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private CommunityUpdateSectionsHandler $handler,
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
            CommunityUpdateSectionsCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
