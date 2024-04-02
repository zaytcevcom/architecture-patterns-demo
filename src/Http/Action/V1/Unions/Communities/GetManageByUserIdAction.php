<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Communities;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Community\GetManageByUserId\CommunityGetManageByUserIdFetcher;
use App\Modules\Union\Query\Community\GetManageByUserId\CommunityGetManageByUserIdQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Exception\AccessDeniedException;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users/{id}/communities/manage',
    description: 'Получение списка администрируемых сообществ пользователя',
    summary: 'Получение списка администрируемых сообществ пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions (User)']
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
    name: 'search',
    description: 'Поисковый запрос',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
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
#[OA\Parameter(
    name: 'fields',
    description: 'Доп. поля (dates, union, counters, country, city, category, subcategory, member, notification)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: null
)]
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class GetManageByUserIdAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private CommunityGetManageByUserIdFetcher $fetcher,
        private Validator $validator,
        private UnionUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $userId = Route::getArgumentToInt($request, 'id');

        if ($identity->id !== $userId) {
            throw new AccessDeniedException();
        }

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => Route::getArgumentToInt($request, 'id')]
            ),
            CommunityGetManageByUserIdQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items, $query->fields)
        );
    }
}
