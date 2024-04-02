<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts;

use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Service\PostSerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/posts/{id}/share',
    description: 'Сообщение для share постом',
    summary: 'Сообщение для share постом',
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
final readonly class ShareAction implements RequestHandlerInterface
{
    public function __construct(
        private PostRepository $postRepository,
        private PostSerializer $postSerializer,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $postId = Route::getArgumentToInt($request, 'id');

        /** @var array{url: string} $post */
        $post = $this->postSerializer->serialize($this->postRepository->getById($postId)->toArray());

        $data = [
            'link' => $post['url'],
            'message' => 'Ссылка на пост: ' . $post['url'],
        ];

        return new JsonDataResponse($data);
    }
}
