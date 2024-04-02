<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Comments;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/posts/{id}/comments/{commentId}',
    description: 'Получение информации о комментарии по его идентификатору',
    summary: 'Получение информации о комментарии по его идентификатору',
    security: [['bearerAuth' => '{}']],
    tags: ['Posts (Comments)'],
    responses: [new ResponseSuccessful()]
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
final readonly class GetByIdAction implements RequestHandlerInterface
{
    public function __construct(
        private PostCommentRepository $postCommentRepository,
        private PostCommentUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $comment = $this->postCommentRepository->getById(
            id: Route::getArgumentToInt($request, 'commentId'),
        );

        return new JsonDataResponse($this->unifier->unifyOne($identity?->id, $comment->toArray()));
    }
}
