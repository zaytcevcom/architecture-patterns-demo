<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Union;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Query\Post\GetPostponedByUnionId\PostGetPostponedByUnionIdFetcher;
use App\Modules\Post\Query\Post\GetPostponedByUnionId\PostGetPostponedByUnionIdQuery;
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
use ZayMedia\Shared\Http\Exception\AccessDeniedException;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataCursorItemsResponse;

#[OA\Get(
    path: '/unions/{id}/postponed',
    description: 'Получение списка отложенных постов объединения',
    summary: 'Получение списка отложенных постов объединения',
    security: [Security::BEARER_AUTH],
    tags: ['Unions'],
    parameters: [new ParameterCursor(), new ParameterCount()],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
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
final readonly class UnionGetPostponedAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostGetPostponedByUnionIdFetcher $fetcher,
        private Validator $validator,
        private PostUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $unionId = Route::getArgumentToInt($request, 'id');

        // todo: проверка прав доступа
        //        if ($identity->id !== $userId) {
        //            throw new AccessDeniedException();
        //        }

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['unionId' => $unionId]
            ),
            PostGetPostponedByUnionIdQuery::class
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
