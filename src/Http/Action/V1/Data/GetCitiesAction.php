<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Modules\Data\Query\City\Get\DataCityGetFetcher;
use App\Modules\Data\Query\City\Get\DataCityGetQuery;
use App\Modules\Data\Service\CitySerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/data/cities',
    description: 'Получение информации о городах',
    summary: 'Получение информации о городах',
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
    name: 'countryId',
    description: 'Идентификатор страны',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 135
)]
#[OA\Parameter(
    name: 'ids',
    description: 'Идентификаторы городов (через запятую)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: null
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
final readonly class GetCitiesAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private DataCityGetFetcher $fetcher,
        private Validator $validator,
        private CitySerializer $serializer
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->denormalizer->denormalizeQuery($request->getQueryParams(), DataCityGetQuery::class);

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}
