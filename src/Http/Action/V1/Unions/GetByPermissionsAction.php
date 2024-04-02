<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Union\GetByPermissions\UnionGetByPermissionsFetcher;
use App\Modules\Union\Query\Union\GetByPermissions\UnionGetByPermissionsQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users/unions',
    description: 'Получение объединений по правам доступа',
    summary: 'Получение объединений по правам доступа',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions (User)']
)]
#[OA\Parameter(
    name: 'permissions',
    description: 'Разрешения (через запятую)',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: 'posts:create,flows:create'
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
final readonly class GetByPermissionsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionGetByPermissionsFetcher $fetcher,
        private Validator $validator,
        private UnionUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => $identity->id]
            ),
            UnionGetByPermissionsQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items, $query->fields)
        );
    }
}
