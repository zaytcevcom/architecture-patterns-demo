<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Like;

use App\Modules\Post\Command\Post\DeleteLike\PostDeleteLikeCommand;
use App\Modules\Post\Command\Post\DeleteLike\PostDeleteLikeHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/posts/{id}/likes',
    description: 'Удаляет лайк с поста<br><br>
    **Коды ошибок**:<br>
    **1** - Доступ запрещен<br>',
    summary: 'Удаляет лайк с поста',
    security: [['bearerAuth' => '{}']],
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
final readonly class DeleteLikeAction implements RequestHandlerInterface
{
    public function __construct(
        private PostDeleteLikeHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new PostDeleteLikeCommand(
            userId: $identity->id,
            postId: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}
