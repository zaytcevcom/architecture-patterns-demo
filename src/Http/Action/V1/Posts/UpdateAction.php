<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Command\Post\Update\PostUpdateCommand;
use App\Modules\Post\Command\Post\Update\PostUpdateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Put(
    path: '/posts/{id}',
    description: 'Редактирование записи',
    summary: 'Редактирование записи',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: null
                ),
                new OA\Property(
                    property: 'photoIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: [100001, 100002, 100003]
                ),
                new OA\Property(
                    property: 'audioIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: [200001, 200002, 200003]
                ),
                new OA\Property(
                    property: 'videoIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: null
                ),
                new OA\Property(
                    property: 'time',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'closeComments',
                    type: 'boolean',
                    example: false
                ),
                new OA\Property(
                    property: 'contactsOnly',
                    type: 'boolean',
                    example: false
                ),
            ]
        )
    ),
    tags: ['Posts']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор поста',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class UpdateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostUpdateHandler $handler,
        private Validator $validator,
        private PostUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $postId = Route::getArgumentToInt($request, 'id');

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'userId' => $identity->id,
                'postId' => $postId,
            ]),
            PostUpdateCommand::class
        );

        $this->validator->validate($command);

        $post = $this->handler->handle($command);

        return new JsonDataResponse($this->unifier->unifyOne($identity->id, $post->toArray()));
    }
}
