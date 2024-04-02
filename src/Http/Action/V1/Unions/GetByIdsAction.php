<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsFetcherCached;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/unions',
    description: 'Получение информации об объединениях по их идентификаторам',
    summary: 'Получение информации об объединениях по их идентификаторам',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions']
)]
#[OA\Parameter(
    name: 'ids',
    description: 'Идентификаторы объединений (через запятую)',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: '1,2'
)]
#[OA\Parameter(
    name: 'fields',
    description: 'Доп. поля (dates, union, counters, country, city, category, subcategory, member, notification, permissions, sections)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: null
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetByIdsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionGetByIdsFetcherCached $fetcher,
        private Validator $validator,
        private UnionUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            $request->getQueryParams(),
            UnionGetByIdsQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: \count($result),
            items: $this->unifier->unify($identity?->id, $result, $query->fields)
        );
    }
}
