<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Members;

use App\Http\Action\Unifier\Union\UnionMembersUnifier;
use App\Modules\Union\Query\Member\Get\UnionMemberGetFetcher;
use App\Modules\Union\Query\Member\Get\UnionMemberGetQuery;
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
    path: '/unions/{id}/members',
    description: 'Возвращает список участников объединения',
    summary: 'Возвращает список участников объединения',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions']
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
    example: '1'
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
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetMembersAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionMemberGetFetcher $fetcher,
        private Validator $validator,
        private UnionMembersUnifier $unifier,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['unionId' => Route::getArgumentToInt($request, 'id')]
            ),
            UnionMemberGetQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity?->id, $result->items)
        );
    }
}
