<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Communities;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Community\GetRecommendation\CommunityGetRecommendationFetcher;
use App\Modules\Union\Query\Community\GetRecommendation\CommunityGetRecommendationQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/communities/recommendations',
    description: 'Рекомендуемые сообщества',
    summary: 'Рекомендуемые сообщества',
    security: [['bearerAuth' => '{}']],
    tags: ['Communities']
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
final readonly class GetRecommendationsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private CommunityGetRecommendationFetcher $fetcher,
        private Validator $validator,
        private UnionUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery($request->getQueryParams(), CommunityGetRecommendationQuery::class);

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items, $query->fields)
        );
    }
}
