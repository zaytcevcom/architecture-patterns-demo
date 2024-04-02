<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Comments;

use App\Modules\Post\Command\PostComment\Create\PostCommentCreateCommand;
use App\Modules\Post\Command\PostComment\Create\PostCommentCreateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/posts/{id}/comments',
    description: 'Создание комментария к посту<br><br>
    **Коды ошибок**:<br>
    **1** - Доступ запрещен<br>
    **2** - Достигнуто ограничение на максимальное кол-во комментариев<br>
    **3** - Достигнут дневной лимит на максимальное кол-во комментариев<br>
    **4** - Дублирующийся пост<br>',
    summary: 'Создание комментария к посту',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'commentId',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'unionId',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Новый коммент!'
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
                    property: 'stickerId',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'uniqueTime',
                    type: 'integer',
                    example: null,
                ),
            ]
        )
    ),
    tags: ['Posts (Comments)']
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
final readonly class CreateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostCommentCreateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'userId' => $identity->id,
                'postId' => Route::getArgumentToInt($request, 'id'),
            ]),
            PostCommentCreateCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
