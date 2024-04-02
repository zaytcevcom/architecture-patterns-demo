<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Modules\Data\Query\City\GetWithSpaces\DataCityGetWithSpacesFetcher;
use App\Modules\Data\Query\City\GetWithSpaces\DataCityGetWithSpacesQuery;
use App\Modules\Data\Service\CitySerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/data/spaces/cities',
    description: 'Получение информации о городах связанных с пространствами',
    summary: 'Получение информации о городах связанных с пространствами',
    tags: ['Data']
)]
#[OA\Parameter(
    name: 'spaceId',
    description: 'Идентификатор пространства',
    in: 'query',
    required: false,
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
final readonly class GetCitiesWithSpacesAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private DataCityGetWithSpacesFetcher $fetcher,
        private Validator $validator,
        private CitySerializer $serializer
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->denormalizer->denormalizeQuery(
            data: $request->getQueryParams(),
            type: DataCityGetWithSpacesQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}
