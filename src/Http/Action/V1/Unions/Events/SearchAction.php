<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Events;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Event\Search\EventSearchFetcher;
use App\Modules\Union\Query\Event\Search\EventSearchQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/events',
    description: 'Глобальный поиск по событиям',
    summary: 'Глобальный поиск по событиям',
    security: [['bearerAuth' => '{}']],
    tags: ['Events']
)]
#[OA\Parameter(
    name: 'spaceId',
    description: 'Идентификатор пространства',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 12
)]
#[OA\Parameter(
    name: 'sphereId',
    description: 'Идентификатор сферы',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: null
)]
#[OA\Parameter(
    name: 'categoryId',
    description: 'Идентификатор категории',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: null
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
final readonly class SearchAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private EventSearchFetcher $fetcher,
        private Validator $validator,
        private UnionUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            data: $request->getQueryParams(),
            type: EventSearchQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items, $query->fields)
        );
    }
}
