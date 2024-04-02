<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Comments;

use App\Modules\Post\Command\PostComment\Restore\PostCommentRestoreCommand;
use App\Modules\Post\Command\PostComment\Restore\PostCommentRestoreHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/posts/{id}/comments/{commentId}/restore',
    description: 'Восстанавливает удаленный комментарий к посту
    **Коды ошибок**:<br><br>
    **1** - Доступ запрещен<br>',
    summary: 'Восстанавливает удаленный комментарий к посту',
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
final readonly class RestoreAction implements RequestHandlerInterface
{
    public function __construct(
        private PostCommentRestoreHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new PostCommentRestoreCommand(
            userId: $identity->id,
            commentId: Route::getArgumentToInt($request, 'commentId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
