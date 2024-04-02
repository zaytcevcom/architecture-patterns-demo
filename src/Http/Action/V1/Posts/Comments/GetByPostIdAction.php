<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Comments;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Post\Query\PostComment\GetByPostId\PostCommentGetByPostIdFetcher;
use App\Modules\Post\Query\PostComment\GetByPostId\PostCommentGetByPostIdQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/posts/{id}/comments',
    description: 'Получение списка комментариев поста',
    summary: 'Получение списка комментариев поста',
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
    description: 'Получение комментариев определенной ветки',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: null
)]
#[OA\Parameter(
    name: 'sort',
    description: 'Сортировка (0 - по убыванию, 1 - по возрастания)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 0
)]
#[OA\Parameter(
    name: 'count',
    description: 'Кол-во которое необходимо получить',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 100
)]
#[OA\Parameter(
    name: 'offset',
    description: 'Смещение',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 0
)]
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class GetByPostIdAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostCommentGetByPostIdFetcher $fetcher,
        private Validator $validator,
        private PostCommentUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['postId' => Route::getArgumentToInt($request, 'id')]
            ),
            PostCommentGetByPostIdQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $items = $this->unifier->unify($identity?->id, $result->items);

        /**
         * @var int $k
         * @var array{array{id: int, postId: int, comments: array{count: int, items: array}|null}} $items
         */
        foreach ($items as $k => $item) {
            if (!isset($item['comments']) || $item['comments']['count'] === 0) {
                continue;
            }

            $subQuery = new PostCommentGetByPostIdQuery(
                postId: $item['postId'],
                commentId: $item['id'],
                count: 3
            );

            $result = $this->fetcher->fetch($subQuery);

            $items[$k]['comments']['items'] = $this->unifier->unify($identity?->id, $result->items);
        }

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $items
        );
    }
}
