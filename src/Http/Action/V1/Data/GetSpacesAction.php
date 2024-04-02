<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Http\Action\Unifier\Data\SpaceUnifier;
use App\Modules\Data\Query\Space\Get\DataSpaceGetFetcher;
use App\Modules\Data\Query\Space\Get\DataSpaceGetQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/data/spaces',
    description: 'Получение информации о пространствах',
    summary: 'Получение информации о пространствах',
    tags: ['Data']
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
    example: '100'
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
    example: '0'
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetSpacesAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private DataSpaceGetFetcher $fetcher,
        private Validator $validator,
        private SpaceUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->denormalizer->denormalizeQuery(
            data: $request->getQueryParams(),
            type: DataSpaceGetQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify(null, $result->items)
        );
    }
}
