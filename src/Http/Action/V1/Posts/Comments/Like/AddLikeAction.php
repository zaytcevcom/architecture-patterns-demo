<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Comments\Like;

use App\Modules\Post\Command\PostComment\AddLike\PostCommentAddLikeCommand;
use App\Modules\Post\Command\PostComment\AddLike\PostCommentAddLikeHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/posts/{id}/comments/{commentId}/likes',
    description: 'Добавляет лайк на комментарий к посту<br><br>
    **Коды ошибок**:<br>
    **1** - Доступ запрещен<br>
    **2** - Достигнуто ограничение на максимальное кол-во лайков<br>
    **3** - Достигнут дневной лимит на максимальное кол-во лайков<br>',
    summary: 'Добавляет лайк на комментарий к посту',
    security: [['bearerAuth' => '{}']],
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
#[OA\Parameter(
    name: 'commentId',
    description: 'Идентификатор комментария',
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
final readonly class AddLikeAction implements RequestHandlerInterface
{
    public function __construct(
        private PostCommentAddLikeHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new PostCommentAddLikeCommand(
            userId: $identity->id,
            commentId: Route::getArgumentToInt($request, 'commentId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
