<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Links;

use App\Modules\Union\Query\UnionLink\GetByUnionId\UnionLinkGetByUnionIdFetcher;
use App\Modules\Union\Query\UnionLink\GetByUnionId\UnionLinkGetByUnionIdQuery;
use App\Modules\Union\Service\UnionLinkSerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/unions/{id}/links',
    description: 'Возвращает список ссылок объединения',
    summary: 'Возвращает список ссылок объединения',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions (Link)']
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
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionLinkGetByUnionIdFetcher $fetcher,
        private Validator $validator,
        private UnionLinkSerializer $serializer,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                [
                    'unionId' => Route::getArgumentToInt($request, 'id'),
                ]
            ),
            UnionLinkGetByUnionIdQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}
