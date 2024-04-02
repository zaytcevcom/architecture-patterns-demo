<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\User;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Query\Post\GetLiked\PostGetLikedFetcher;
use App\Modules\Post\Query\Post\GetLiked\PostGetLikedQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ParameterCount;
use ZayMedia\Shared\Helpers\OpenApi\ParameterCursor;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Helpers\OpenApi\Security;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataCursorItemsResponse;

#[OA\Get(
    path: '/users/{id}/posts/liked',
    description: 'Получение списка понравившихся постов пользователя',
    summary: 'Получение списка понравившихся постов пользователя',
    security: [Security::BEARER_AUTH],
    tags: ['Posts (User)'],
    parameters: [new ParameterCursor(), new ParameterCount()],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор пользователя',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
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
final readonly class UserGetLikedAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostGetLikedFetcher $fetcher,
        private Validator $validator,
        private PostUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => Route::getArgumentToInt($request, 'id')]
            ),
            PostGetLikedQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $items = $this->unifier->unify($identity->id, $result->items);

        return new JsonDataCursorItemsResponse(
            count: $result->count,
            items: $items,
            cursor: $result->cursor
        );
    }
}
